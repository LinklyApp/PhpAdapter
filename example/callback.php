<?php

require __DIR__ . '/../vendor/autoload.php';
require './config.php';

use League\OAuth2\Client\Provider\MementoAuthHelper;

try {
    /** @var MementoAuthHelper $mementoAuthHelper */
    $mementoAuthHelper->callback();
    header('Location: ' . '//'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']));
} catch (Exception $e) {
    dd($e);
}