<?php

/*
 @nom: common
 @auteur: Idleman (idleman@idleman.fr)
 @description: Page incluse dans tous (ou presque) les fichiers du projet, inclus les entitées SQL et récupère/traite les variables de requetes
 */

$cookiedir = '';
if(dirname($_SERVER['SCRIPT_NAME'])!='/') $cookiedir=dirname($_SERVER["SCRIPT_NAME"]).'/';
session_set_cookie_params(0, $cookiedir);
session_start();
mb_internal_encoding('UTF-8'); // UTF8 pour fonctions mb_*
$start=microtime(true);
require_once('constant.php');
if (!defined('LANGUAGE')) {
    define('LANGUAGE', LANGAGE); // ancienne constante encore utilisée
    trigger_error('Please use, in "constant.php", LANGUAGE instead of LANGAGE');
}
require_once('RainTPL.php');
require_once('i18n.php');
class_exists('Plugin') or require_once('Plugin.class.php');
class_exists('MysqlEntity') or require_once('MysqlEntity.class.php');
class_exists('Feed') or require_once('Feed.class.php');
class_exists('Event') or require_once('Event.class.php');
class_exists('Functions') or require_once('Functions.class.php');
class_exists('User') or require_once('User.class.php');
class_exists('Folder') or require_once('Folder.class.php');
class_exists('Configuration') or require_once('Configuration.class.php');
class_exists('Opml') or require_once('Opml.class.php');


//error_reporting(E_ALL);

//Calage de la date
date_default_timezone_set('Europe/Paris');

$myUser = (isset($_SESSION['currentUser'])?unserialize($_SESSION['currentUser']):false);
$feedManager = new Feed();
$eventManager = new Event();
$userManager = new User();
if (empty($myUser)) $myUser = $userManager->existAuthToken();
$folderManager = new Folder();
$configurationManager = new Configuration();
$conf = $configurationManager->getAll();

//Instanciation du template
$tpl = new RainTPL();
//Definition des dossiers de template
raintpl::configure("base_url", null );
raintpl::configure("tpl_dir", './templates/'.DEFAULT_THEME.'/' );
raintpl::configure("cache_dir", "./cache/tmp/" );

i18n_init();


$view = '';
$tpl->assign('i18n_js',$i18n_js);
$tpl->assign('myUser',$myUser);
$tpl->assign('feedManager',$feedManager);
$tpl->assign('eventManager',$eventManager);
$tpl->assign('userManager',$userManager);
$tpl->assign('folderManager',$folderManager);
$tpl->assign('configurationManager',$configurationManager);
$tpl->assign('synchronisationCode',$configurationManager->get('synchronisationCode'));

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

?>
