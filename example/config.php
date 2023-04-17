<?php

$linklyClientId = 'php-adapter-example';
$linklyClientSecret = 'secret';
$linklyEnvironment = 'local';  // options are "prod", "beta", "local"

// Only required when using the sso.
// When only the export-invoices is used, in combination with the client-credentials flow, then this is not required.
$linklyRedirectUri = 'http://linklyphpadapter.test/example/sso/callback.php';
