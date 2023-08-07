<?php

require __DIR__ . '/../../vendor/autoload.php';
require '../config.php';
require '../initialize.php';

use Linkly\OAuth2\Client\Helpers\LinklySsoHelper;
use function Linkly\OAuth2\Client\Helpers\dd;

try {
    /** @var LinklySsoHelper $linklySsoHelper */
    $linklySsoHelper->callback();
    header('Location: ' . '//'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']));
    exit;
} catch (Exception $e) {
    dd($e);
}