<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Helpers\CodeChallenge;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Memento extends AbstractProvider
{
    use BearerAuthorizationTrait;

    public $domain = 'https://accounts.mementobilling.com';
    public $apiDomain = 'https://api.mementobilling.com';

    public $betaDomain = 'https://accounts.acc.mementobilling.com';
    public $betaApiDomain = 'https://api.acc.mementobilling.com';

    public $localDomain = 'https://localhost:5001';
    public $localApiDomain = 'https://localhost:5005';

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

    /**
     * Get provider url to fetch user details
     *
     * @param AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->getApiDomainUrl() . '/api/users/info';
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
        return ['openid profile memento-api'];
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

        $options['code_verifier'] = $_SESSION['code_verifier'];

        unset($_SESSION['code_verifier']);

        $options['response_type'] = 'code';

        return parent::getAccessToken($grant, $options);
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
//        if ($response->getStatusCode() >= 400) {
//            throw new Exception($response, $data);
//        } elseif (isset($data['error'])) {
//            throw new Exception($response, $data);
//        }

        return true;
    }


    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response
     * @param AccessToken $token
     * @return \League\OAuth2\Client\Provider\ResourceOwnerInterface
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
