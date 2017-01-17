<?php

// https://github.com/lelag/otphp
// http://sebsauvage.net/wiki/doku.php?id=totp

require_once dirname(__FILE__).'/otphp/lib/otphp.php';


# Il faut faire comme ça : https://python-totp.herokuapp.com/
# Ça génère un OTP aléatoire et le QR qui correspond. FreeOTP le récupère
# d'un coup. Idéal à placer sur la gestion du compte pour activer l'OTP sans se
# soucier du secret.

$pass = $argv[1];
$totp1 = new \OTPHP\TOTP($pass, array('interval'=>30, 'digits'=>8, 'digest'=>'sha1'));


while( True ){
    echo str_pad($totp1->now(), $totp1->digits, '0', STR_PAD_LEFT)."\n";
    sleep(1);
}
