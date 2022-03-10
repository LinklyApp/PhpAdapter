<?php

require __DIR__ . '/../vendor/autoload.php';
require './config.php';
require '../src/Helpers/GenericHelpers.php';

use Memento\OAuth2\Client\Helpers\MementoSsoHelper;

?>

<?php /** @var MementoSsoHelper $mementoAuthHelper */
if (!$mementoAuthHelper->isAuthenticated()) : ?>
    <form action="sso/login.php">
        <button>Login</button>
    </form>
<?php else : ?>
    <form action="sso/index.php">
        <button>See user info</button>
    </form>
<?php endif; ?>

<form action="export-invoices/index.php">
    <button>Client Credentials</button>
</form>
