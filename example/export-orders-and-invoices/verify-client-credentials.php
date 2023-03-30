<?php

require __DIR__ . '/../../vendor/autoload.php';
require '../config.php';
require '../initialize.php';

/** @var $linklyInvoiceHelper LinklyOrderHelper */

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Linkly\OAuth2\Client\Helpers\LinklyOrderHelper;
use function Linkly\OAuth2\Client\Helpers\dd;

try {
    dd($linklyInvoiceHelper->verifyClientCredentials());
} catch (IdentityProviderException $e) {
    echo '<h1>Error: Client is not valid</h1>';
    dd($e->getResponseBody());
}
