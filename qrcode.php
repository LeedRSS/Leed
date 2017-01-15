<?php

require_once('common.php');
##if (empty($myUser)) exit();

require_once('phpqrcode.php');

#$qrcode = $_['q'];

function chargeVarRequest() {
    foreach (func_get_args() as $arg) {
        global ${$arg};
        if (array_key_exists($arg, $_REQUEST)) {
            $valeur = $_REQUEST[$arg];
        } else {
            $valeur = '';
        }
        ${$arg} = $valeur;
    }
}

echo "<pre>";

switch(array_keys($_REQUEST)[0]) {
    case 'qr':
        chargeVarRequest('label', 'user', 'key', 'url', 'issuer');
        $qrCode = "otpauth://totp/{$label}:{$user}?secret={$key}&issuer={$issuer}";
        break;
    default:
        $qrCode = 'bla';
}

QRcode::png($qrCode);
