<?php

namespace Memento\OAuth2\Client\Provider;

use Exception;
use GuzzleHttp\Client as HttpClient;
use Memento\OAuth2\Client\Helpers\CodeChallenge;
use Memento\OAuth2\Client\Helpers\GenericHelpers;
use Memento\OAuth2\Client\Provider\Invoice\MementoInvoice;
use Memento\OAuth2\Client\Provider\User\MementoUser;
use Psr\Http\Message\ResponseInterface;

use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use League\OAuth2\Client\Provider\AbstractProvider;



class MementoProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public $domain = 'https://api.mementobilling.com';
    public $apiDomain = 'https://api.mementobilling.com';

    public $betaDomain = 'https://api.acc.mementobilling.com';
    public $betaApiDomain = 'https://api.acc.mementobilling.com';

    public $localDomain = 'https://localhost:5001';
    public $localApiDomain = 'https://localhost:5001';
//    public $localApiDomain = 'https://b058fafb-5a65-4db7-ad64-1e5632856836.mock.pstmn.io';

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

    public function getBaseImportInvoiceUrl()
    {
        return $this->getApiDomainUrl() . '/api/import/invoices';
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

    public function sendInvoice(MementoInvoice $invoice, AccessToken $clientCredentialsAccessToken)
    {
        $method  = self::METHOD_POST;
        $url     = $this->getBaseImportInvoiceUrl();

        $options = ['body' => json_encode($invoice->toArray())];
        $request = $this->getAuthenticatedRequest($method, $url, $clientCredentialsAccessToken, $options);

        return $this->getParsedResponse($request);
    }


    public function getAuthorizationHeaders($token = null)
    {
        return [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
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
        return ['openid profile offline_access memento-api'];
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
     * @throws IdentityProviderException|Exception
     * @param ResponseInterface $response
     * @param array $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
//        if ($response->getStatusCode() >= 400) {
//            throw new Exception($response, $data);
//        } elseif (isset($data['error'])) {
//            throw new Exception($response, $data);
//        }
    }


    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response
     * @param AccessToken $token
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        $user = new MementoUser($response);
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
