<?php

require __DIR__ . '/../../vendor/autoload.php';
require '../config.php';
require '../initialize.php';

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Linkly\OAuth2\Client\Helpers\LinklyOrderHelper;
use function Linkly\OAuth2\Client\Helpers\dd;

/** @var $linklyOrderHelper LinklyOrderHelper */

try {
    $orderAsJson = file_get_contents('./mockOrder.json');
    $response = $linklyOrderHelper->sendOrder($orderAsJson);
    echo '<h1>JSON Order was successfully exported to Linkly</h1>';
    dd($response);
} catch (IdentityProviderException $e) {
    echo '<h1>Error: Order was not exported</h1>';
    dd($e->getResponseBody());
}