<?php

/*
 @nom: settings
 @auteur: Idleman (idleman@idleman.fr)
 @description: Page de gestion de toutes les préférences/configurations administrateur
 */

require_once('header.php');

// gestion de la langue
$languageList = $i18n->languages;
$tpl->assign('languageList',$languageList);
$tpl->assign('currentLanguage',$configurationManager->get('language'));

// gestion des thèmes
$themesDir = 'templates/';
$dirs = scandir($themesDir);
foreach($dirs as $dir){
    if(is_dir($themesDir.$dir) && !in_array($dir,array(".","..")) ){
        $themeList[]=$dir;
    }
}
sort($themeList);
$tpl->assign('themeList',$themeList);
$tpl->assign('currentTheme',$configurationManager->get('theme'));

//autres variables de configuration
$tpl->assign('feeds',$feedManager->populate('name'));
$tpl->assign('folders',$folderManager->populate('name'));
$tpl->assign('synchronisationType',$configurationManager->get('synchronisationType'));
$tpl->assign('synchronisationEnableCache',$configurationManager->get('synchronisationEnableCache'));
$tpl->assign('synchronisationForceFeed',$configurationManager->get('synchronisationForceFeed'));
$tpl->assign('articleDisplayAnonymous', $configurationManager->get('articleDisplayAnonymous'));
$tpl->assign('articleDisplayLink', $configurationManager->get('articleDisplayLink'));
$tpl->assign('articleDisplayDate', $configurationManager->get('articleDisplayDate'));
$tpl->assign('articleDisplayAuthor', $configurationManager->get('articleDisplayAuthor'));
$tpl->assign('articleDisplayHomeSort', $configurationManager->get('articleDisplayHomeSort'));
$tpl->assign('articleDisplayFolderSort', $configurationManager->get('articleDisplayFolderSort'));
$tpl->assign('articleDisplayMode', $configurationManager->get('articleDisplayMode'));
$tpl->assign('optionFeedIsVerbose', $configurationManager->get('optionFeedIsVerbose'));

//Suppression de l'état des plugins inexistants
Plugin::pruneStates();

//Récuperation des plugins
$tpl->assign('plugins',Plugin::getAll());

$view = "settings";
require_once('footer.php'); ?>
