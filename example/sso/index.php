<?php

require __DIR__ . '/../../vendor/autoload.php';
require '../config.php';

use Memento\OAuth2\Client\Helpers\GenericHelpers;
use Memento\OAuth2\Client\Helpers\MementoSsoHelper;

/** @var MementoSsoHelper $mementoAuthHelper */
if (!$mementoAuthHelper->isAuthenticated()) {
    header('Location: ' . '//' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']));
} ?>


<?php
$user = $mementoAuthHelper->getUser();
GenericHelpers::returnAsJson(['userInfo' => $user->toArray(), 'token' => $_SESSION['token']]);
?>
