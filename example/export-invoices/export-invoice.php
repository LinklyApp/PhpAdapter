<?php

require __DIR__ . '/../../vendor/autoload.php';
require '../config.php';
require '../initialize.php';

use Memento\OAuth2\Client\Helpers\MementoInvoiceHelper;
use Memento\OAuth2\Client\Provider\Invoice\MementoInvoice;
use function Memento\OAuth2\Client\Helpers\dd;

/** @var $mementoInvoiceHelper MementoInvoiceHelper */

try {
    $invoiceAsJson = json_decode(file_get_contents('./mockInvoice.json'), true);
    $invoice = new MementoInvoice($invoiceAsJson);
    $response = $mementoInvoiceHelper->sendInvoice($invoice);
    echo '<h1>Invoice successfully exported to Memento</h1>';
} catch (Exception $e) {
    dd($e);
}