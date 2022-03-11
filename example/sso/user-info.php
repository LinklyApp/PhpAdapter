<?php

require __DIR__ . '/../../vendor/autoload.php';
require '../config.php';
require '../initialize.php';

use Memento\OAuth2\Client\Helpers\MementoSsoHelper;
use function Memento\OAuth2\Client\Helpers\returnAsJson;

/** @var MementoSsoHelper $mementoSsoHelper */

if (!$mementoSsoHelper->isAuthenticated()) {
    header('Location: ' . '//' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']));
    exit;
} ?>

<?php
$user = $mementoSsoHelper->getUser();
returnAsJson(['userInfo' => $user->toArray()]);
?>