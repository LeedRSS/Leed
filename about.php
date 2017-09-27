<?php

/*
 @nom: about
 @auteur: Idleman (http://blog.idleman.fr)
 @description: Page "A propos" d'information contextuelles sur le projet
 */

require_once('header.php');

$tpl->assign('otpEnabled', $configurationManager->get('otpEnabled'));
$view = 'about';
require_once('footer.php');

?>
