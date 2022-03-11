<?php

require __DIR__ . '/../../vendor/autoload.php';
require '../config.php';
require '../initialize.php';

use Memento\OAuth2\Client\Helpers\MementoSsoHelper;
use function Memento\OAuth2\Client\Helpers\dd;

try {
    /** @var MementoSsoHelper $mementoSsoHelper */
    $mementoSsoHelper->callback();
    header('Location: ' . '//'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']));
    exit;
} catch (Exception $e) {
    dd($e);
}