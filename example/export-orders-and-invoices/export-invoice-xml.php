<?php

require __DIR__ . '/../../vendor/autoload.php';
require '../config.php';
require '../initialize.php';

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Linkly\OAuth2\Client\Helpers\LinklyOrderHelper;
use function Linkly\OAuth2\Client\Helpers\dd;

/** @var $linklyOrderHelper LinklyOrderHelper */

try {
    $invoiceAsXml = file_get_contents('./mockInvoice.xml');
    $response = $linklyOrderHelper->sendInvoice($invoiceAsXml);
    echo '<h1>XML Invoice successfully exported to Linkly</h1>';
    dd($response);
} catch (IdentityProviderException $e) {
    echo '<h1>Error: Invoice was not exported</h1>';
    dd($e->getResponseBody());
}