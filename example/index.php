<?php

require __DIR__ . '/../vendor/autoload.php';
require './config.php';

use League\OAuth2\Client\Helpers\Helpers;
use League\OAuth2\Client\Provider\MementoAuthHelper;

?>

<?php if (!MementoAuthHelper::isAuthenticated()) : ?>
    <form action="login.php">
        <button>Login</button>
    </form>
<?php else : ?>
    <?php
    $user = MementoAuthHelper::getUser();
    Helpers::returnAsJson($user->toArray());
    ?>
<?php endif; ?>