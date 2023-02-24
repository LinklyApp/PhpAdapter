<?php

require __DIR__ . '/../../vendor/autoload.php';
require '../config.php';
require '../initialize.php';

use Linkly\OAuth2\Client\Helpers\LinklyInvoiceHelper;
use Linkly\OAuth2\Client\Provider\Exception\LinklyProviderException;
use function Linkly\OAuth2\Client\Helpers\dd;

/** @var $linklyInvoiceHelper LinklyInvoiceHelper */

try {
    $invoiceAsJson = file_get_contents('./mockInvoice.json');
    $response = $linklyInvoiceHelper->sendInvoice($invoiceAsJson);
    echo '<h1>JSON Invoice successfully exported to Linkly</h1>';
    dd($response);
} catch (LinklyProviderException $e) {
    echo '<h1>Error: Invoice was not exported</h1>';
    dd($e->getResponseBody());
}