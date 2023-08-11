<?php

namespace Linkly\OAuth2\Client\Provider;

use Exception;
use GuzzleHttp\Client as HttpClient;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Linkly\OAuth2\Client\Provider\Exception\LinklyProviderException;
use Linkly\OAuth2\Client\Provider\User\LinklyUser;
use Psr\Http\Message\ResponseInterface;
use function Linkly\OAuth2\Client\Helpers\dd;
use function Linkly\OAuth2\Client\Helpers\isJson;
use function Linkly\OAuth2\Client\Helpers\isXml;


class LinklyProvider extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public string $domain = 'https://api.linkly.me';
    public string $apiDomain = 'https://api.linkly.me';
    public string $webDomain = 'https://web.linkly.me';

    public string $betaDomain = 'https://api.acc.linkly.dev';
    public string $betaApiDomain = 'https://api.acc.linkly.dev';
    public string $betaWebDomain = 'https://web.acc.linkly.dev';

    public string $localDomain = 'https://localhost:5001';
    public string $localApiDomain = 'https://localhost:5001';
    public string $localWebDomain = 'https://localhost:4000';

    public string $environment = 'prod';
    private array $environmentOptions = ['prod', 'beta', 'local'];

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
    public function getBaseAuthorizationUrl(): string
    {
        return $this->getDomainUrl() . '/connect/authorize';
    }

    /**
     * Get url to change address
     *
     * @param string $clientId
     * @param array $options
     * @return string
     */
    public function getChangeAddressUrl(array $options = []): string
    {
        $base = $this->getWebDomainUrl();
        $base .= '/ext/shop-address';
        $query = http_build_query($options);

        return $this->appendQuery($base, $query);
    }

    /**
     * Get url to link client
     *
     * @param array $options
     * @return string
     */
    public function getLinkClientUrl(array $options = []): string
    {
        $base = $this->getWebDomainUrl();
        $base .= '/apps';

        $this->state = $this->getRandomState();

        // check if $options[returnUrl] has query params
        if ($options['returnUrl'] && strpos($options['returnUrl'], '?') !== false) {
            $options['returnUrl'] .= '&state=' . $this->state;
        } else {
            $options['returnUrl'] .= '?state=' . $this->state;
        }

        $query = http_build_query($options);

        return $this->appendQuery($base, $query);
    }


    /**
     * Get access token url to retrieve token
     *
     * @param array $params
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return $this->getDomainUrl() . '/connect/token';
    }

    public function getBaseExternalOrderUrl(): string
    {
        return $this->getApiDomainUrl() . '/external-api/orders';
    }

    public function getBaseExternalInvoiceUrl(): string
    {
        return $this->getApiDomainUrl() . '/external-api/invoices';
    }

    public function getBaseExternalClientUrl(): string
    {
        return $this->getApiDomainUrl() . '/external-api/clients';
    }

    public function getBaseExternalAddressUrl(): string
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
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return $this->getDomainUrl() . '/connect/userinfo';
    }

    /**
     * @return array
     * @throws IdentityProviderException | Exception
     */
    public function sendOrder($clientCredentialsToken, $data): array
    {
        $method = self::METHOD_POST;
        $url = $this->getBaseExternalOrderUrl();
        $options = ['body' => $data];

        if (isJson($data)) {
            $options['headers'] = ['Content-Type' => 'application/json'];
        } else {
            throw new Exception('Invalid order format. Needs to be valid json');
        }

        $request = $this->getAuthenticatedRequest($method, $url, $clientCredentialsToken, $options);
        return $this->getParsedResponse($request);
    }

    /**
     * @return array
     * @throws IdentityProviderException | Exception
     */
    public function sendInvoice($clientCredentialsToken, $data): array
    {
        $method = self::METHOD_POST;
        $url = $this->getBaseExternalInvoiceUrl();
        $options = ['body' => $data];

        if (isJson($data)) {
            $options['headers'] = ['Content-Type' => 'application/json'];
        } else if (isXml($data)) {
            $url .= '/peppol-xml';
            $options['headers'] = ['Content-Type' => 'application/xml'];
        } else {
            throw new Exception('Invalid invoice format. Needs to be either valid json or xml');
        }

        $request = $this->getAuthenticatedRequest($method, $url, $clientCredentialsToken, $options);
        return $this->getParsedResponse($request);
    }

    /**
     * @param array $addressData All of "billingAddressId", "billingAddressVersion", "shippingAddressId", and "shippingAddressVersion".
     * @return array
     * @throws IdentityProviderException
     */
    public function hasAddressBeenChanged($token, array $addressData): array
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
     *
     * @param array $options {
     *     An array of options.
     *
     * @return array
     * @throws IdentityProviderException
     * @var string $clientId Unique identifier for the client (Optional).
     * @var string $clientSecret Unique secret for the client (Optional).
     *
     */
    public function verifyClientCredentials(array $options = []): array
    {
        if (isset($options['clientId'])) {
            $this->clientId = $options['clientId'];
        }
        if (isset($options['clientSecret'])) {
            $this->clientSecret = $options['clientSecret'];
        }

        $method = self::METHOD_POST;
        $url = $this->getBaseExternalClientUrl() . '/verify';

        $clientCredentialsToken = $this->getAccessToken('client_credentials');

        $request = $this->getAuthenticatedRequest($method, $url, $clientCredentialsToken);
        return $this->getParsedResponse($request);
    }


    public function getAuthorizationHeaders($token = null): array
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
    protected function getDefaultScopes(): array
    {
        return ['openid profile offline_access linkly-external-api'];
    }

    protected function getAuthorizationParameters(array $options): array
    {
        $options = parent::getAuthorizationParameters($options);

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['pkce_code'] = $this->pkceCode;

        return $options;
    }

    public function getAccessToken($grant, array $options = [])
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['pkce_code']) && $grant == 'authorization_code') {
            $this->pkceCode = $_SESSION['pkce_code'];
            unset($_SESSION['pkce_code']);
        }

        $options['response_type'] = 'code';

        return parent::getAccessToken($grant, $options);
    }

    /**
     * @return string|null
     */
    protected function getPkceMethod()
    {
        return static::PKCE_METHOD_S256;
    }

    /**
     * Check a provider response for errors.
     *
     * @link   https://developer.github.com/v3/#client-errors
     * @link   https://developer.github.com/v3/oauth/#common-errors-for-the-access-token-request
     * @throws IdentityProviderException
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
     * Get the domain for the domain based on the environment.
     *
     * @return string
     */
    public function getDomainUrl(): string
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
     * Get the url for the api domain based on the environment.
     *
     * @return string
     */
    public function getApiDomainUrl(): string
    {
        if ($this->environment === 'local') {
            return $this->localApiDomain;
        }
        if ($this->environment === 'beta') {
            return $this->betaApiDomain;
        }
        return $this->apiDomain;
    }

    /**
     * Get the url for the web domain based on the environment.
     *
     * @return string
     */
    public function getWebDomainUrl(): string
    {
        if ($this->environment === 'local') {
            return $this->localWebDomain;
        }
        if ($this->environment === 'beta') {
            return $this->betaWebDomain;
        }
        return $this->webDomain;
    }
}
