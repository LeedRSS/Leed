<?php

/*
 @nom: common
 @auteur: Idleman (idleman@idleman.fr)
 @description: Page incluse dans tous (ou presque) les fichiers du projet, inclus les entitées SQL et récupère/traite les variables de requetes
 */

define('VERSION_NUMBER_CODE','1.6.1');
define('VERSION_NAME_CODE','dev');

/* ----------MAJ de la version du constant.php--------------------- */
if (is_writable('constant.php')) {
    $content = file_get_contents('constant.php');
    preg_match('#define\(\'VERSION_NUMBER\',\'([A-Za-z0-9.]+)\'\);?#',$content,$matches_version);
    preg_match('#define\(\'VERSION_NAME\',\'([A-Za-z0-9.]+)\'\);?#',$content,$matches_name);
    if ($matches_version[1]!=VERSION_NUMBER_CODE or $matches_name[1]!=VERSION_NAME_CODE)
    {
        $content = preg_replace('#define\(\'VERSION_NUMBER\',\'([A-Za-z0-9.]+)\'\);?#','define(\'VERSION_NUMBER\',\''.VERSION_NUMBER_CODE.'\');', $content);
        $content = preg_replace('#define\(\'VERSION_NAME\',\'([A-Za-z0-9.]+)\'\);?#','define(\'VERSION_NAME\',\''.VERSION_NAME_CODE.'\');', $content);
        file_put_contents('constant.php', $content);
    }
};
/* ---------------------------------------------------------------- */
// Mise en place d'un timezone par default pour utiliser les fonction de date en php
$timezone_default = 'Europe/Paris'; // valeur par défaut :)
date_default_timezone_set($timezone_default);
$timezone_phpini = ini_get('date.timezone');
if (($timezone_phpini!='') && (strcmp($timezone_default, $timezone_phpini))) {
    date_default_timezone_set($timezone_phpini);
}
/* ---------------------------------------------------------------- */
$cookiedir = '';
if(dirname($_SERVER['SCRIPT_NAME'])!='/') $cookiedir=dirname($_SERVER["SCRIPT_NAME"]).'/';
session_set_cookie_params(0, $cookiedir);
session_start();
mb_internal_encoding('UTF-8'); // UTF8 pour fonctions mb_*
$start=microtime(true);
require_once('constant.php');
require_once('RainTPL.php');
require_once('i18n.php');
require_once('otphp/lib/otphp.php');
class_exists('Functions') or require_once('Functions.class.php');
class_exists('Plugin') or require_once('Plugin.class.php');
class_exists('MysqlEntity') or require_once('MysqlEntity.class.php');
class_exists('Update') or require_once('Update.class.php');
class_exists('Feed') or require_once('Feed.class.php');
class_exists('Event') or require_once('Event.class.php');
class_exists('User') or require_once('User.class.php');
class_exists('Folder') or require_once('Folder.class.php');
class_exists('Configuration') or require_once('Configuration.class.php');
class_exists('Opml') or require_once('Opml.class.php');
class_exists('Logger') or require_once('Logger.class.php');


//error_reporting(E_ALL);

//Calage de la date
date_default_timezone_set('Europe/Paris');

$configurationManager = new Configuration();
$conf = $configurationManager->getAll();

$update = new Update();
$resultUpdate = $update->executePatch();

$userManager = new User();
$myUser = (isset($_SESSION['currentUser'])?unserialize($_SESSION['currentUser']):false);
if (empty($myUser)) {
    /* Pas d'utilisateur dans la session ?
     * On tente de récupérer une nouvelle session avec un jeton. */
    $myUser = User::existAuthToken();
    $_SESSION['currentUser'] = serialize($myUser);
}

$myUserConfs = $myUser instanceof User ?
    $myUser->getConf()
    : $userManager->getConf();

$theme = $myUserConfs->theme;
$language = $myUserConfs->language;

$feedManager = new Feed();
$eventManager = new Event();
$folderManager = new Folder();

//Instanciation du template
$tpl = new RainTPL();
//Definition des dossiers de template
raintpl::configure("base_url", null );
raintpl::configure("tpl_dir", './templates/'.$theme.'/' );
raintpl::configure("cache_dir", "./cache/tmp/" );

i18n_init($language, dirname(__FILE__).'/templates/'.$theme.'/');
if ($resultUpdate) die (_t('LEED_UPDATE_MESSAGE'));

$view = '';
$tpl->assign('myUser',$myUser);
$tpl->assign('myUserConfs',$myUserConfs);
$tpl->assign('feedManager',$feedManager);
$tpl->assign('eventManager',$eventManager);
$tpl->assign('userManager',$userManager);
$tpl->assign('folderManager',$folderManager);
$tpl->assign('configurationManager',$configurationManager);
$tpl->assign('synchronisationCode',$configurationManager->get('synchronisationCode'));

$articleDisplayAnonymous = $configurationManager->get('articleDisplayAnonymous');
$tpl->assign('articleDisplayAnonymous',$articleDisplayAnonymous);

$isAlwaysDisplayed = ($articleDisplayAnonymous=='1') || ($myUser!=false);
$tpl->assign('isAlwaysDisplayed',$isAlwaysDisplayed);

//Récuperation et sécurisation de toutes les variables POST et GET
$_ = array();
foreach($_POST as $key=>$val){
    $_[$key]=Functions::secure($val, 2); // on ne veut pas d'addslashes
}
foreach($_GET as $key=>$val){
    $_[$key]=Functions::secure($val, 2); // on ne veut pas d'addslashes
}

$tpl->assign('_',$_);
$tpl->assign('action','');

//Inclusion des plugins
Plugin::includeAll();
// pour inclure aussi les traductions des plugins dans les js
$tpl->assign('i18n_js',$i18n_js);

?>
