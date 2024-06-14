<?php

namespace Linkly\OAuth2\Client\Helpers;

use Exception;
use Firebase\JWT\JWT;
use InvalidArgumentException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Linkly\OAuth2\Client\Provider\LinklyProvider;
use Linkly\OAuth2\Client\Provider\User\LinklyUser;
use League\OAuth2\Client\Token\AccessToken;

class LinklySsoHelper
{
    /** @var LinklyProvider $provider */
    private $provider;

    public function __construct(LinklyProvider $provider)
    {
        $this->provider = $provider;
        $this->provider->startSession();
    }

    public function authorizeRedirect()
    {
        if (isset($_GET['code'])) {
            throw new \Exception('Code challenge is set. Use callback()');
        }

        $authUrl = $this->provider->getAuthorizationUrl();

        $this->provider->setSessionVariable('state', $this->provider->getState());

        header('Location: ' . $authUrl);
        exit;
    }

    public function callback()
    {
        if (isset($_GET['error'])) {
            throw new \Exception($_GET['error']);
        }

        if (!isset($_GET['code'])) {
            throw new \Exception('Code challenge is not set. Use login()');
        }

        $this->checkIfValidState();

        $token = $this->provider->getAccessToken('authorization_code', [
            'code' => $_GET['code'],
        ]);

        $this->provider->setSessionVariable('token', $token);
    }

    /**
     *
     * @param array $options {
     *     An array of options.
     *
     * @var string $returnUrl URL for redirection after changing Address.
     * @var string $clientId Unique identifier for the client.
     *
     * @throws Exception
     */
    public function changeAddressRedirect(array $options = [])
    {
        $this->renewTokenIfExpired();

        $requiredOptions = ['returnUrl', 'clientId'];

        foreach ($requiredOptions as $option) {
            if (!isset($options[$option])) {
                throw new InvalidArgumentException("Missing required option: $option");
            }
        }

        $changeAddressUrl = $this->provider->getChangeAddressUrl($options);
        header('Location: ' . $changeAddressUrl);
        exit;
    }

    /**
     * Links a client with provided options.
     *
     * @param array $options {
     *     An array of options.
     *
     * @var string $returnUrl URL for redirection after linking.
     * @var string $clientName Unique identifier for the client.
     * @var string $allowedCorsOrigin Allowed domain for cross-origin requests.
     * @var string $postLogoutRedirectUri URL for redirection after logout.
     * @var string $redirectUri URL for redirection after authentication.
     * }
     */
    public function linkClientRedirect(array $options = [])
    {
        $requiredOptions = ['returnUrl', 'clientName', 'allowedCorsOrigin', 'postLogoutRedirectUri', 'redirectUri'];

        foreach ($requiredOptions as $option) {
            if (!isset($options[$option])) {
                throw new InvalidArgumentException("Missing required option: $option");
            }
        }

        $linkClientUrl = $this->provider->getLinkClientUrl($options);

        $this->provider->setSessionVariable('state', $this->provider->getState());
        header('Location: ' . $linkClientUrl);
        exit;
    }

    /**
     * @throws Exception
     */
    public function linkClientCallback()
    {
        $this->checkIfValidState();
    }

    public function isAuthenticated()
    {
        if ($this->provider->getSessionVariable('token') == null) {
            return false;
        }

        $this->renewTokenIfExpired();

        return true;
    }


    public function logout()
    {
        $this->provider->deleteSession();
    }

    public function getUser(): LinklyUser
    {
        $this->renewTokenIfExpired();

        /** @var LinklyUser $linklyUser */
        $linklyUser = $this->provider->getResourceOwner($this->provider->getSessionVariable('token'));
        return $linklyUser;
    }

    public function getSubject()
    {
        /** @var AccessToken $currentToken */
        return $this->getJWTPayload()->sub;
    }

    public function getEmail()
    {
        /** @var AccessToken $currentToken */
        return $this->getJWTPayload()->email;
    }

    public function getJWTPayload()
    {
        $token = $this->provider->getSessionVariable('token');

        if (!$token) {
            $this->provider->deleteSession();
            $this->authorizeRedirect();
        }

        $tks = explode('.', $token);
        list($headb64, $bodyb64, $cryptob64) = $tks;
        return JWT::jsonDecode(JWT::urlsafeB64Decode($bodyb64));
    }

    public function getToken()
    {
        return $this->provider->getSessionVariable('token');
    }

    /**
     *
     * @param array $options {
     *     An array of options.
     *
     * @var string $clientId Unique identifier for the client (Optional).
     * @var string $clientSecret Unique secret for the client (Optional).
     *
     * @return array
     * @throws IdentityProviderException
     */
    public function verifyClientCredentials(array $options = [])
    {
        return $this->provider->verifyClientCredentials($options);
    }

    /**
     * @param array $addressData All of "billingAddressId", "billingAddressVersion", "shippingAddressId", and "shippingAddressVersion".
     * @return array
     * @throws IdentityProviderException
     */
    public function hasAddressBeenChanged(array $addressData)
    {
        $this->renewTokenIfExpired();
        return $this->provider->hasAddressBeenChanged($this->provider->getSessionVariable('token'), $addressData);
    }

    private function renewTokenIfExpired()
    {
        try {
            $currentToken = $this->provider->getSessionVariable('token');
            if (is_null($currentToken) ||  !$currentToken->hasExpired()) {
                return;
            }

            $newAccessToken = $this->provider->getAccessToken('refresh_token', [
                'refresh_token' => $currentToken->getRefreshToken()
            ]);

            $this->provider->setSessionVariable('token', $newAccessToken);
        } catch (\Exception $exception) {
            $this->provider->deleteSession();
            $this->authorizeRedirect();
        }
    }

    private function checkIfValidState()
    {
        if (empty($_GET['state']) || ($_GET['state'] !== $this->provider->getSessionVariable('state'))) {
            throw new \Exception('Session state does not match returned state');
        }
    }
}
