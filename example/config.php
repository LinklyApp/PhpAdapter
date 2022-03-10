<?php

use Memento\OAuth2\Client\Helpers\MementoSsoHelper;
use Memento\OAuth2\Client\Helpers\MementoInvoiceHelper;
use Memento\OAuth2\Client\Provider\MementoProvider;

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

$mementoAuthHelper = new MementoSsoHelper($provider);
$mementoInvoiceHelper = new MementoInvoiceHelper($provider);