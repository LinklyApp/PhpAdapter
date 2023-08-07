<?php

require __DIR__ . '/../../vendor/autoload.php';
require '../config.php';
require '../initialize.php';

use Linkly\OAuth2\Client\Helpers\LinklySsoHelper;
use function Linkly\OAuth2\Client\Helpers\returnAsJson;

/** @var LinklySsoHelper $linklySsoHelper */

if (!$linklySsoHelper->isAuthenticated()) {
    header('Location: ' . '//' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']));
    exit;
} ?>

<?php
$user = $linklySsoHelper->getUser();
returnAsJson(['userInfo' => $user->toArray()]);
?>