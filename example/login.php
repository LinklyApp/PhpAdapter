<?php

require __DIR__ . '/../vendor/autoload.php';
require './config.php';

use League\OAuth2\Client\Provider\MementoAuthHelper;

MementoAuthHelper::authorize();
