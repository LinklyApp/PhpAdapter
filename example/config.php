<?php

use League\OAuth2\Client\Provider\MementoAuthHelper;
use League\OAuth2\Client\Provider\MementoProvider;

require '../src/Helpers/Helpers.php';

$mementoClientId = 'test-oauth';
$mementoClientSecret = 'secret';
$mementoRedirectUri = 'http://oauth2-memento.test/example/callback.php';
$mementoEnvironment = 'local';  // options are "prod", "beta", "local"

$provider = new MementoProvider([
    'clientId'          => $mementoClientId,
    'clientSecret'      => $mementoClientSecret,
    'redirectUri'       => $mementoRedirectUri,
    'environment'       => $mementoEnvironment
]);

$mementoAuthHelper = new MementoAuthHelper($provider);