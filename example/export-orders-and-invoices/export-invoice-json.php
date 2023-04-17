<?php

require __DIR__ . '/../../vendor/autoload.php';
require '../config.php';
require '../initialize.php';

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Linkly\OAuth2\Client\Helpers\LinklyOrderHelper;
use function Linkly\OAuth2\Client\Helpers\dd;

/** @var $linklyOrderHelper LinklyOrderHelper */

try {
    $invoiceAsJson = file_get_contents('./mockInvoice.json');
    $response = $linklyOrderHelper->sendInvoice($invoiceAsJson);
    echo '<h1>JSON Invoice was successfully exported to Linkly</h1>';
    dd($response);
} catch (IdentityProviderException $e) {
    echo '<h1>Error: Invoice was not exported</h1>';
    dd($e->getResponseBody());
}