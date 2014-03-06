<?php

/*
 @nom: about
 @auteur: Idleman (idleman@idleman.fr)
 @description: Page "A propos" d'information contextuelles sur le projet
 */

require_once('header.php');
require_once('constant.php');

$tpl->assign('VERSION_NUMBER',VERSION_NUMBER);
$tpl->assign('VERSION_NAME',VERSION_NAME);
$view = 'about';
require_once('footer.php');

?>
