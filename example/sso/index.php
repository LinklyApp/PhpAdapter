<?php

require __DIR__ . '/../../vendor/autoload.php';
require '../config.php';
require '../initialize.php';

use Memento\OAuth2\Client\Helpers\MementoSsoHelper;

/** @var MementoSsoHelper $mementoSsoHelper */

?>
<h1>Memento SSO Example</h1>
<div><a href="../index.php">Back</a></div>

<h2>Options</h2>
<?php if (!$mementoSsoHelper->isAuthenticated()) : ?>
    <div><a href="login.php">Login</a></div>
<?php else : ?>
    <div><a href="user-info.php">See user info</a></div>
    <div><a href="token.php">Token</a></div>
    <div><a href="logout.php">Logout</a></div>
<?php endif; ?>
