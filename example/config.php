<?php

$mementoClientId = 'test-oauth';
$mementoClientSecret = 'secret';
$mementoEnvironment = 'local';  // options are "prod", "beta", "local"

// Only required when using the sso.
// When only the export-invoices is used, in combination with the client-credentials flow, then this is not required.
$mementoRedirectUri = 'http://oauth2-memento.test/example/sso/callback.php';
