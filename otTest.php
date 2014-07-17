<?php

// https://github.com/lelag/otphp
// http://sebsauvage.net/wiki/doku.php?id=totp

require_once dirname(__FILE__).'/otphp/lib/otphp.php';

$totp1 = new \OTPHP\TOTP('22222222', array('interval'=>30, 'digits'=>8, 'digest'=>'sha1'));
$totp512 = new \OTPHP\TOTP('22222222', array('interval'=>30, 'digits'=>8, 'digest'=>'sha512'));
print_r($totp);


while( True ){
    echo "1 ", str_pad($totp1->now(), $totp1->digits, '0', STR_PAD_LEFT)."\n";
    echo "512 ", str_pad($totp512->now(), $totp512->digits, '0', STR_PAD_LEFT)."\n";
    sleep(1);
}

// OTP verified for current time
// $totp->verify(492039); // => true
// //30s later
// $totp->verify(492039); // => false
