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
        $this->startLinklySession();
    }

    public function authorizeRedirect()
    {
        if (isset($_GET['code'])) {
            throw new \Exception('Code challenge is set. Use callback()');
        }

        $authUrl = $this->provider->getAuthorizationUrl();
        $_SESSION['linklyState'] = $this->provider->getState();
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

        $_SESSION['token'] = $token;
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
        $_SESSION['linklyState'] = $this->provider->getState();
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
        if (!isset($_SESSION['token'])) {
            return false;
        }

        $this->renewTokenIfExpired();

        return true;
    }


    public function logout()
    {
        unset($_SESSION['token']);
    }

    public function getUser(): LinklyUser
    {
        $this->renewTokenIfExpired();

        /** @var LinklyUser $linklyUser */
        $linklyUser = $this->provider->getResourceOwner($_SESSION['token']);
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
        $token = $_SESSION['token'];

        if (!$token) {
            session_destroy();
            $this->authorizeRedirect();
        }

        $tks = explode('.', $token);
        list($headb64, $bodyb64, $cryptob64) = $tks;
        return JWT::jsonDecode(JWT::urlsafeB64Decode($bodyb64));
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
        return $this->provider->hasAddressBeenChanged($_SESSION['token'], $addressData);
    }

    private function renewTokenIfExpired()
    {
        try {
            $currentToken = $_SESSION['token'];
            if (!$currentToken->hasExpired()) {
                return;
            }

            $newAccessToken = $this->provider->getAccessToken('refresh_token', [
                'refresh_token' => $currentToken->getRefreshToken()
            ]);

            $_SESSION['token'] = $newAccessToken;
        } catch (\Exception $exception) {
            session_destroy();
            $this->authorizeRedirect();
        }
    }

    private function startLinklySession()
    {
        $name = 'LinklySession';

        if ($name === session_name()) {
            return;
        }

        session_write_close();
        session_name($name);
        if (!isset($_COOKIE[$name])) {
            $_COOKIE[$name] = session_create_id();
        }
        session_id($_COOKIE[$name]);
        session_start();
    }


    private function checkIfValidState()
    {
        if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['linklyState'])) {
            throw new \Exception('linklyState does not match returned state');
        }
    }
}
