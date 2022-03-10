<?php

require __DIR__ . '/../../vendor/autoload.php';
require '../config.php';

use Memento\OAuth2\Client\Helpers\MementoSsoHelper;

try {
    /** @var MementoSsoHelper $mementoAuthHelper */
    $mementoAuthHelper->callback();
    header('Location: ' . '//'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']));
} catch (Exception $e) {
    dd($e);
}