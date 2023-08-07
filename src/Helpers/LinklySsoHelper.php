<?php

namespace Linkly\OAuth2\Client\Helpers;

use Exception;
use Firebase\JWT\JWT;
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

    public function changeAddressRedirect(array $options = [])
    {
        $this->renewTokenIfExpired();

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

        $_SESSION['linklyState'] = $this->provider->getState();

        $linkClientUrl = $this->provider->getLinkClientUrl($options);
        header('Location: ' . $linkClientUrl);
        exit;
    }

    /*
     * Links a client with provided options.
     * @return array {
     *    An array of options.
     *   @var string $client_id Unique identifier for the client.
     *   @var string $client_secret Secret for the client.
     * }
     *
     */
    public function linkClientCallback()
    {
        $this->checkIfValidState();

        if (!isset($_GET['client_id']) || !isset($_GET['client_secret'])) {
            throw new Exception('Both client_id and client_secret must be set');
        }

        $client_id = filter_input(INPUT_GET, 'client_id', FILTER_SANITIZE_STRING);
        $client_secret = filter_input(INPUT_GET, 'client_secret', FILTER_SANITIZE_STRING);

        if (!$client_id) {
            throw new Exception('client_id is either not set or contains invalid characters');
        }

        if (!$client_secret) {
            throw new Exception('client_secret is either not set or contains invalid characters');
        }

        return array(
            'client_id' => $client_id,
            'client_secret' => $client_secret
        );
    }

    public function isAuthenticated()
    {
        if (!isset($_SESSION['token'])) {
            return false;
        }

        $this->renewTokenIfExpired();

        return true;
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
     * @throws IdentityProviderException
     */
    public function verifyClientCredentials()
    {
        return $this->provider->verifyClientCredentials();
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
