<?php

namespace Linkly\OAuth2\Client\Helpers;

use Firebase\JWT\JWT;
use http\Exception;
use Linkly\OAuth2\Client\Provider\Invoice\LinklyInvoice;
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

    public function authorize()
    {
        if (isset($_GET['code'])) {
            throw new \Exception('Code challenge is set. Use callback()');
        }

        $authUrl = $this->provider->getAuthorizationUrl();
        $_SESSION['oauth2state'] = $this->provider->getState();
        header('Location: ' . $authUrl);
        exit;
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
        if ( isset($_GET['error'])) {
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
            $this->authorize();
        }

        $tks = explode('.', $token);
        list($headb64, $bodyb64, $cryptob64) = $tks;
        return JWT::jsonDecode(JWT::urlsafeB64Decode($bodyb64));
    }

    public function verifyClient()
    {
        return $this->provider->verifyClient();
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
            $this->authorize();
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
        if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
            echo 'Session state: ' . $_SESSION['oauth2state'] ?? null;
            echo '<br>';
            echo 'Redirected state: ' . $_GET['state'];
            echo '<br>';

            unset($_SESSION['oauth2state']);
            exit('State not equal');
        }
    }
}
