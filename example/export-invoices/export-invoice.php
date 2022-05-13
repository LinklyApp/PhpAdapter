<?php

require __DIR__ . '/../../vendor/autoload.php';
require '../config.php';
require '../initialize.php';

use Memento\OAuth2\Client\Helpers\MementoInvoiceHelper;
use Memento\OAuth2\Client\Provider\Invoice\MementoInvoice;
use function Memento\OAuth2\Client\Helpers\dd;

/** @var $mementoInvoiceHelper MementoInvoiceHelper */

try {
//    $invoiceAsJson = file_get_contents('./mockInvoice.json');
    $invoiceAsXml = file_get_contents('./mockInvoice.xml');

//    $response = $mementoInvoiceHelper->sendInvoice($invoiceAsJson);
    $response = $mementoInvoiceHelper->sendInvoice($invoiceAsXml);

    if ($response['errors']) {
        echo '<h1>Error: Invoice was not exported</h1>';
    } else {
        echo '<h1>Invoice successfully exported to Memento</h1>';
    }
    dd($response);
} catch (Exception $e) {
    dd($e);
}