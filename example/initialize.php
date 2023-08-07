<?php

use Linkly\OAuth2\Client\Helpers\LinklySsoHelper;
use Linkly\OAuth2\Client\Helpers\LinklyOrderHelper;
use Linkly\OAuth2\Client\Provider\LinklyProvider;

$provider = new LinklyProvider([
    'clientId'          => $linklyClientId,
    'clientSecret'      => $linklyClientSecret,
    'environment'       => $linklyEnvironment,
    'redirectUri'       => $linklyRedirectUri ?? null, // not required with just the client-credentials grant
]);

$linklySsoHelper = new LinklySsoHelper($provider);
$linklyOrderHelper = new LinklyOrderHelper($provider);