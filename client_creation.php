<?php
require_once ( 'includes/MangoPaySDK/mangoPayApi.inc');
$api = new MangoPay\MangoPayApi();
var_dump("ddd");

// Change this fields to set you own client.
$client = $api->Clients->Create(
    'xarxaintegral2',
    'xarxa de projectes CIC',
    'info@coopfunding.net'
);

var_dump("ddd");
// you receive your password here, note it down and keep in secret
print($client->Passphrase);
print('done');
?>
