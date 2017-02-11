<?php

/*
 @nom: settings
 @auteur: Idleman (idleman@idleman.fr)
 @description: Page de gestion de toutes les préférences/configurations administrateur
 */

require_once('header.php');

$wrongLogin = !empty($wrongLogin);
$tpl->assign('wrongLogin',$wrongLogin);
$tpl->assign('otpEnabled', $configurationManager->get('otpEnabled'));

if($myUser !== false) {
    $tpl->assign('serviceUrl', rtrim($_SERVER['HTTP_HOST'].$cookiedir,'/'));

    $logger = new Logger('settings');
    $tpl->assign('logs',$logger->flushLogs());

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
    $tpl->assign('synchronisationType',$myUserConfs->synchronisationType);
    $tpl->assign('synchronisationEnableCache',$myUserConfs->synchronisationEnableCache);
    $tpl->assign('synchronisationForceFeed',$myUserConfs->synchronisationForceFeed);
    $tpl->assign('articleDisplayLink', $myUserConfs->articleDisplayLink);
    $tpl->assign('articleDisplayDate', $myUserConfs->articleDisplayDate);
    $tpl->assign('articleDisplayAuthor', $myUserConfs->articleDisplayAuthor);
    $tpl->assign('articleDisplayHomeSort', $myUserConfs->articleDisplayHomeSort);
    $tpl->assign('articleDisplayFolderSort', $myUserConfs->articleDisplayFolderSort);
    $tpl->assign('articleDisplayMode', $myUserConfs->articleDisplayMode);
    $tpl->assign('optionFeedIsVerbose', $myUserConfs->optionFeedIsVerbose);

    $tpl->assign('userList', $userManager->getUserList());

    //Suppression de l'état des plugins inexistants
    Plugin::pruneStates();

    //Récuperation des plugins
    $tpl->assign('plugins',Plugin::getAll());
}

$view = "settings";
require_once('footer.php');

?>
