<?php

require __DIR__ . '/../../vendor/autoload.php';
require '../config.php';
require '../initialize.php';

use Linkly\OAuth2\Client\Helpers\LinklySsoHelper;

/** @var LinklySsoHelper $linklySsoHelper */
$linklySsoHelper->changeAddressRedirect(['clientId' => $linklyClientId, 'returnUrl' => '//'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/index.php']);
