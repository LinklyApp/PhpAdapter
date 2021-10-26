<?php

require __DIR__ . '/../vendor/autoload.php';
require './config.php';
require '../src/Helpers/Helpers.php';

use Memento\OAuth2\Client\Helpers\MementoAuthHelper;

?>

<?php /** @var MementoAuthHelper $mementoAuthHelper */
if (!$mementoAuthHelper->isAuthenticated()) : ?>
    <form action="login.php">
        <button>Login</button>
    </form>
<?php else : ?>
    <?php
    $user = $mementoAuthHelper->getUser();
    returnAsJson($user->toArray());
    ?>
<?php endif; ?>