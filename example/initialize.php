<?php

use Memento\OAuth2\Client\Helpers\MementoSsoHelper;
use Memento\OAuth2\Client\Helpers\MementoInvoiceHelper;
use Memento\OAuth2\Client\Provider\MementoProvider;

$provider = new MementoProvider([
    'clientId'          => $mementoClientId,
    'clientSecret'      => $mementoClientSecret,
    'redirectUri'       => $mementoRedirectUri,
    'environment'       => $mementoEnvironment
]);

$mementoSsoHelper = new MementoSsoHelper($provider);
$mementoInvoiceHelper = new MementoInvoiceHelper($provider);