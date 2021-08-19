<?php

namespace League\OAuth2\Client\Provider;

use http\Exception;
use League\OAuth2\Client\Provider\User\MementoUser;

class MementoAuthHelper
{
    /** @var MementoProvider $provider  */
    private $provider;

    public function __construct(MementoProvider $provider)
    {
        $this->provider = $provider;
        $this->startMementoSession();
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
        if (!isset($_GET['code'])) {
            throw new \Exception('Code challenge is not set. Use login()');
        }

        $this->checkIfValidState();

        $token = $this->provider->getAccessToken('authorization_code', [
            'code' => $_GET['code'],
        ]);

        $_SESSION['token'] = $token;
    }

    public function getUser(): MementoUser
    {
        $this->renewTokenIfExpired();

        /** @var MementoUser $mementoUser */
        $mementoUser = $this->provider->getResourceOwner($_SESSION['token']);
        return $mementoUser;
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

    private function startMementoSession()
    {
        $name = 'MementoSession';

        if ($name !== session_name()) {
            session_write_close();
            session_name($name);
            if(!isset($_COOKIE[$name]))
            {
                $_COOKIE[$name] = session_create_id();
            }
            session_id($_COOKIE[$name]);
            session_start();
        }
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
