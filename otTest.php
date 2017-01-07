<?php

// https://github.com/lelag/otphp
// http://sebsauvage.net/wiki/doku.php?id=totp

require_once dirname(__FILE__).'/otphp/lib/otphp.php';

$pass = '42432526';
$pass = 'abcdefgh';
$totp1 = new \OTPHP\TOTP($pass, array('interval'=>30, 'digits'=>8, 'digest'=>'sha1'));
$totp2 = new \OTPHP\TOTP(strtoupper($pass), array('interval'=>30, 'digits'=>8, 'digest'=>'sha1'));


while( True ){
    echo "1 ", str_pad($totp1->now(), $totp1->digits, '0', STR_PAD_LEFT)."\n";
    echo "2 ", str_pad($totp2->now(), $totp2->digits, '0', STR_PAD_LEFT)."\n";
    sleep(1);
}

// OTP verified for current time
// $totp->verify(492039); // => true
// //30s later
// $totp->verify(492039); // => false
