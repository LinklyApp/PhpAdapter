<?php

require __DIR__ . '/../vendor/autoload.php';
require 'config.php';
require 'initialize.php';

/** @var $linklyInvoiceHelper LinklyInvoiceHelper */

use Linkly\OAuth2\Client\Helpers\LinklyInvoiceHelper;
use Linkly\OAuth2\Client\Provider\Exception\LinklyProviderException;
use function Linkly\OAuth2\Client\Helpers\dd;

try {
    dd($linklyInvoiceHelper->verifyClientCredentials());
} catch (LinklyProviderException $e) {
    echo '<h1>Error: Client is not valid</h1>';
    dd($e->getResponseBody());
}
