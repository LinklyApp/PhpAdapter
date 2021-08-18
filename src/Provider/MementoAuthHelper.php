<?php

namespace League\OAuth2\Client\Provider;

use http\Exception;
use League\OAuth2\Client\Helpers\Helpers;
use League\OAuth2\Client\Provider\User\MementoUser;

class MementoAuthHelper
{
    /** @var MementoProvider $mementoProvider  */
    private static $mementoProvider;

    public static function authorize()
    {
        self::startMementoSession();

        if (isset($_GET['code'])) {
            throw new \Exception('Code challenge is set. Use callback()');
        }

        $authUrl = self::getProvider()->getAuthorizationUrl();
        $_SESSION['oauth2state'] = self::getProvider()->getState();
        header('Location: ' . $authUrl);
        exit;
    }

    public static function isAuthenticated()
    {
        self::startMementoSession();
        if (!isset($_SESSION['token'])) {
            return false;
        }

        self::renewTokenIfExpired();

        return true;
    }

    public static function callback()
    {
        self::startMementoSession();

        if (!isset($_GET['code'])) {
            throw new \Exception('Code challenge is not set. Use login()');
        }

        self::checkIfValidState();

        $token = self::getProvider()->getAccessToken('authorization_code', [
            'code' => $_GET['code'],
        ]);

        $_SESSION['token'] = $token;
    }


    public static function getUser(): MementoUser
    {
        self::startMementoSession();
        self::renewTokenIfExpired();

        /** @var MementoUser $mementoUser */
        $mementoUser = self::getProvider()->getResourceOwner($_SESSION['token']);
        return $mementoUser;
    }

    private static function getProvider(): MementoProvider
    {
        self::startMementoSession();
        self::validateProviderSettings();


        if (!isset($_SESSION['provider']) || self::providerSettingsChanged()) {
            $_SESSION['provider'] = [
                'clientId'      => MEMENTO_CLIENT_ID,
                'clientSecret'  => MEMENTO_CLIENT_SECRET,
                'redirectUri'   => MEMENTO_REDIRECT_URI,
                'environment'   => MEMENTO_ENVIRONMENT
            ];

            self::$mementoProvider = new MementoProvider($_SESSION['provider']);
        }

        if (!self::$mementoProvider) {
            self::$mementoProvider = new MementoProvider($_SESSION['provider']);
        }

        return self::$mementoProvider;
    }

    private static function providerSettingsChanged(){
        if ($_SESSION['provider']['clientId'] !== MEMENTO_CLIENT_ID) return true;
        if ($_SESSION['provider']['clientSecret'] !== MEMENTO_CLIENT_SECRET) return true;
        if ($_SESSION['provider']['redirectUri'] !== MEMENTO_REDIRECT_URI) return true;
        if ($_SESSION['provider']['environment'] !== MEMENTO_ENVIRONMENT) return true;

        return false;
    }

    private static function validateProviderSettings()
    {
        if (!defined('MEMENTO_CLIENT_ID') || empty(MEMENTO_CLIENT_ID)) {
            unset($_SESSION);
            throw new \Exception('MEMENTO_CLIENT_ID is required');
        }
        if (!defined('MEMENTO_CLIENT_SECRET') || empty(MEMENTO_CLIENT_SECRET)) {
            unset($_SESSION);
            throw new \Exception('MEMENTO_CLIENT_SECRET is required');
        }
        if (!defined('MEMENTO_REDIRECT_URI') || empty(MEMENTO_REDIRECT_URI)) {
            unset($_SESSION);
            throw new \Exception('MEMENTO_REDIRECT_URI is required');
        }
        if (!defined('MEMENTO_ENVIRONMENT') || empty(MEMENTO_ENVIRONMENT)) {
            unset($_SESSION);
            throw new \Exception('MEMENTO_ENVIRONMENT is required');
        }

        return true;
    }


    private static function startMementoSession()
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

    private static function renewTokenIfExpired()
    {
        if (!isset($_SESSION['token'])) {
            throw new \Exception('Token is not set');
        }
        $currentToken = $_SESSION['token'];
        if (!$currentToken->hasExpired()) {
            return;
        }

        $newAccessToken = self::getProvider()->getAccessToken('refresh_token', [
            'refresh_token' => $currentToken->getRefreshToken()
        ]);
        $_SESSION['token'] = $newAccessToken;
    }

    private static function checkIfValidState()
    {
        if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

            echo 'Session state: ' . $_SESSION['oauth2state'];
            echo '<br>';
            echo 'Redirected state: ' . $_GET['state'];
            echo '<br>';

            unset($_SESSION['oauth2state']);
            exit('State not equal');
        }
    }
}