<?php

require __DIR__ . '/../../vendor/autoload.php';
require '../config.php';
require '../initialize.php';

use Linkly\OAuth2\Client\Helpers\LinklySsoHelper;
use function Linkly\OAuth2\Client\Helpers\dd;

/** @var LinklySsoHelper $linklySsoHelper */

?>
<h1>Linkly SSO Example</h1>
<div><a href="../index.php">Back</a></div>

<h2>Options</h2>
<?php if (!$linklySsoHelper->isAuthenticated()) : ?>
    <div><a href="login.php">Login</a></div>
<?php else : ?>
    <div><a href="user-info.php">See user info</a></div>
    <div><a href="token.php">Token</a></div>
    <div><a href="change-address.php">Change address</a></div>
    <div><a href="logout.php">Logout</a></div>

    <div>Has address been changed: <?php
        $user = $linklySsoHelper->getUser();

        $addressData['billingAddressId'] = $user->getBillingAddress()->getId();
        $addressData['billingAddressVersion'] = $user->getBillingAddress()->getVersion();
        $addressData['shippingAddressId'] = $user->getShippingAddress()->getId();
        $addressData['shippingAddressVersion'] = $user->getShippingAddress()->getVersion();

        echo $linklySsoHelper->hasAddressBeenChanged($addressData)['message'] ?></div>
<?php endif; ?>
