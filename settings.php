<?php

/*
 @nom: settings
 @auteur: Idleman (http://blog.idleman.fr)
 @description: Page de gestion de toutes les préférences/configurations administrateur
 */

require_once('header.php');

$tpl->assign('serviceUrl', rtrim($_SERVER['HTTP_HOST'].$cookiedir,'/'));

$logger = new Logger('settings');
$tpl->assign('logs',$logger->flushLogs());

// gestion de la langue
$tpl->assign('languageList',$i18n->translatedLanguages);
$tpl->assign('currentLanguage',$configurationManager->get('language'));

$wrongLogin = !empty($wrongLogin);
$tpl->assign('wrongLogin',$wrongLogin);

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
