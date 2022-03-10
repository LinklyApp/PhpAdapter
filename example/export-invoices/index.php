<?php

require __DIR__ . '/../../vendor/autoload.php';
require '../config.php';

use Memento\OAuth2\Client\Helpers\MementoInvoiceHelper;
use Memento\OAuth2\Client\Helpers\GenericHelpers;
use Memento\OAuth2\Client\Provider\Invoice\MementoInvoice;

try {
    /** @var MementoInvoiceHelper $mementoInvoiceHelper */
    $clientCredentialsAccessToken = $mementoInvoiceHelper->getClientCredentialsAccessToken();

    $invoiceAsJson = json_decode(file_get_contents('./mockInvoice.json'), true);

    $invoice = new MementoInvoice($invoiceAsJson);
    /** @var $mementoInvoiceHelper MementoInvoiceHelper */
    $response = $mementoInvoiceHelper->sendInvoice($invoice);
    GenericHelpers::returnAsJson($response);
} catch (Exception $e) {
    GenericHelpers::dd($e);
}