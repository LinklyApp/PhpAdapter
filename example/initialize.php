<?php

use Memento\OAuth2\Client\Helpers\MementoSsoHelper;
use Memento\OAuth2\Client\Helpers\MementoInvoiceHelper;
use Memento\OAuth2\Client\Provider\MementoProvider;

$provider = new MementoProvider([
    'clientId'          => $mementoClientId,
    'clientSecret'      => $mementoClientSecret,
    'environment'       => $mementoEnvironment,
    'redirectUri'       => $mementoRedirectUri ?? null, // not required with just the client-credentials grant
]);

$mementoSsoHelper = new MementoSsoHelper($provider);
$mementoInvoiceHelper = new MementoInvoiceHelper($provider);