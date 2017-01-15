<?php


/*
 * phpinfo();
exit();
 */
require_once('common.php');
##if (empty($myUser)) exit();

require_once('phpqrcode.php');

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

$methode = array_keys($_REQUEST)[0];
switch($methode) {
    case 'qr':
        chargeVarRequest('label', 'user', 'key');
        $qrCode = "otpauth://totp/{$label}:{$user}?secret={$key}";
        break;
    case 'txt':
        $qrCode = substr($_SERVER['QUERY_STRING'], 1+strlen($methode));
        break;
    default:
        $qrCode = '';
}

chargeVarRequest('_qrSize', '_qrMargin');
if (empty($_qrSize))   $_qrSize   = 3;
if (empty($_qrMargin)) $_qrMargin = 4;

// public static function png($text, $outfile = false, $level = QR_ECLEVEL_L, $size = 3, $margin = 4, $saveandprint=false)
QRcode::png($qrCode, false, 'QR_LEVEL_H', $_qrSize, $_qrMargin);
