<?php

namespace Linkly\OAuth2\Client\Provider;

use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Linkly\OAuth2\Client\Helpers\CodeChallenge;
use Linkly\OAuth2\Client\Provider\Exception\LinklyProviderException;
use Linkly\OAuth2\Client\Provider\User\LinklyUser;
use Psr\Http\Message\ResponseInterface;
use function Linkly\OAuth2\Client\Helpers\dd;
use function Linkly\OAuth2\Client\Helpers\isJson;
use function Linkly\OAuth2\Client\Helpers\isXml;


class LinklyProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public $domain = 'https://api.linkly.me';
    public $apiDomain = 'https://api.linkly.me';

    public $betaDomain = 'https://api.acc.linkly.dev';
    public $betaApiDomain = 'https://api.acc.linkly.dev';

    public $localDomain = 'https://localhost:5001';
    public $localApiDomain = 'https://localhost:5001';

    public $environment = 'prod';
    private $environmentOptions = ['prod', 'beta', 'local'];

    /**
     * @var CodeChallenge
     */
    private $codeChallenge;

    public function __construct($options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);

        if (!empty($options['environment'])) {
            if (!in_array($options['environment'], $this->environmentOptions)) {
                $message = 'Invalid environment, available options are "prod", "beta" and "local"';
                throw new \InvalidArgumentException($message);
            }

            $this->environment = $options['environment'];
        }

        if ($options['environment'] === 'local') {
            $client_options = $this->getAllowedClientOptions($options);

            // This allows locally signed ssl certificates
            $httpClient = new HttpClient(
                array_merge(
                    array_intersect_key($options, array_flip($client_options)),
                    ['verify' => false]
                )
            );

            $this->setHttpClient($httpClient);
        }
    }

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->getDomainUrl() . '/connect/authorize';
    }


    /**
     * Get access token url to retrieve token
     *
     * @param array $params
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->getDomainUrl() . '/connect/token';
    }

    public function getBaseExternalInvoiceUrl()
    {
        return $this->getApiDomainUrl() . '/external-api/invoices';
    }

    public function getBaseExternalClientUrl()
    {
        return $this->getApiDomainUrl() . '/external-api/clients';
    }

    public function getBaseExternalAddressUrl()
    {
        return $this->getApiDomainUrl() . '/external-api/addresses';
    }

    /**
     * Get provider url to fetch user details
     *
     * @param AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->getDomainUrl() . '/connect/userinfo';
    }

    /**
     * @return array
     * @throws LinklyProviderException|IdentityProviderException
     */
    public function sendInvoice($clientCredentialsToken, $data)
    {
        $method = self::METHOD_POST;
        $url = $this->getBaseExternalInvoiceUrl();
        $options = ['body' => $data];

        if (isJson($data)) {
            $url .= '/json';
            $options['headers'] = ['Content-Type' => 'application/json'];
        } elseif (isXml($data)) {
            $url .= '/xml';
            $options['headers'] = ['Content-Type' => 'application/xml'];
        } else {
            throw new Exception('Invalid invoice type');
        }

        $request = $this->getAuthenticatedRequest($method, $url, $clientCredentialsToken, $options);
        return $this->getParsedResponse($request);
    }

    /**
     * @param array $addressData All of "billingAddressId", "billingAddressVersion", "shippingAddressId", and "shippingAddressVersion".
     * @return array
     * @throws LinklyProviderException|IdentityProviderException
     */
    public function hasAddressBeenChanged($token, array $addressData)
    {
        $url = $this->getBaseExternalAddressUrl() . '/has-changed';


        $options = [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($addressData)
        ];

        $request = $this->getAuthenticatedRequest(self::METHOD_POST, $url, $token, $options);
        return $this->getParsedResponse($request);
    }

    /**
     * @return array
     * @throws LinklyProviderException|IdentityProviderException
     */
    public function verifyClientCredentials()
    {
        $method = self::METHOD_POST;
        $url = $this->getBaseExternalClientUrl() . '/verify';

        $clientCredentialsToken = $this->getAccessToken('client_credentials');

        $request = $this->getAuthenticatedRequest($method, $url, $clientCredentialsToken);
        return $this->getParsedResponse($request);
    }


    public function getAuthorizationHeaders($token = null)
    {
        return [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];
    }

    /**
     * Get the default scopes used by this provider.
     *
     * This should not be a complete list of all scopes, but the minimum
     * required for the provider user interface!
     *
     * @return array
     */
    protected function getDefaultScopes()
    {
        return ['openid profile offline_access linkly-external-api'];
    }

    protected function getAuthorizationParameters(array $options)
    {
        $options = parent::getAuthorizationParameters($options);

        $this->codeChallenge = new CodeChallenge();
        $this->codeChallenge->generate();

        $options['code_challenge'] = $this->codeChallenge->challenge;
        $options['code_challenge_method'] = $this->codeChallenge->challengeMethod;

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['code_verifier'] = $this->codeChallenge->verifier;

        return $options;
    }

    public function getAccessToken($grant, array $options = [])
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['code_verifier'])) {
            $options['code_verifier'] = $_SESSION['code_verifier'];
            unset($_SESSION['code_verifier']);
        }

        $options['response_type'] = 'code';

        return parent::getAccessToken($grant, $options);
    }

    /**
     * Check a provider response for errors.
     *
     * @link   https://developer.github.com/v3/#client-errors
     * @link   https://developer.github.com/v3/oauth/#common-errors-for-the-access-token-request
     * @throws LinklyProviderException|IdentityProviderException
     * @param ResponseInterface $response
     * @param array $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            throw LinklyProviderException::clientException($response, $data);
        } elseif (isset($data['error'])) {
            throw LinklyProviderException::oauthException($response, $data);
        }
    }


    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response
     * @param AccessToken $token
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        $user = new LinklyUser($response);
        return $user->setDomain($this->getApiDomainUrl());
    }

    /**
     * Get the base Facebook URL.
     */
    private function getDomainUrl(): string
    {
        if ($this->environment === 'local') {
            return $this->localDomain;
        }
        if ($this->environment === 'beta') {
            return $this->betaDomain;
        }
        return $this->domain;
    }

    /**
     * Get the base Graph API URL.
     */
    private function getApiDomainUrl(): string
    {
        if ($this->environment === 'local') {
            return $this->localApiDomain;
        }
        if ($this->environment === 'beta') {
            return $this->betaApiDomain;
        }
        return $this->apiDomain;
    }
}
