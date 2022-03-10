<?php

require __DIR__ . '/../../vendor/autoload.php';
require '../config.php';

use Memento\OAuth2\Client\Helpers\MementoSsoHelper;

/** @var MementoSsoHelper $mementoAuthHelper */
$mementoAuthHelper->authorize();
