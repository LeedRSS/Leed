<?php 

/*
 @nom: settings
 @auteur: Idleman (idleman@idleman.fr)
 @description: Page de gestion de toutes les préférences/configurations administrateur
 */

require_once('header.php'); 



$tpl->assign('feeds',$feedManager->populate('name'));
$tpl->assign('folders',$folderManager->populate('name'));
$tpl->assign('synchronisationType',$configurationManager->get('synchronisationType'));
$tpl->assign('articleDisplayAnonymous', $configurationManager->get('articleDisplayAnonymous'));
$tpl->assign('articleDisplayLink', $configurationManager->get('articleDisplayLink')); 
$tpl->assign('articleDisplayDate', $configurationManager->get('articleDisplayDate')); 
$tpl->assign('articleDisplayAuthor', $configurationManager->get('articleDisplayAuthor'));
$tpl->assign('articleDisplayContent', $configurationManager->get('articleDisplayContent')); 
$tpl->assign('articleView', $configurationManager->get('articleView'));
//Récuperation des plugins  
$tpl->assign('plugins',Plugin::getAll());

$view = "settings";
require_once('footer.php'); ?>
