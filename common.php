<?php

/*
 @nom: common
 @auteur: Idleman (http://blog.idleman.fr)
 @description: Page incluse dans tous (ou presque) les fichiers du projet, inclus les entitées SQL et récupère/traite les variables de requetes
 */

define('LEED_VERSION_NUMBER','1.13.0');
define('LEED_VERSION_NAME','dev');

/* Assure la compatibilité des greffons utilisant ces anciennes constantes.
 * Cela doit rester en place jusque Leed v2.0.
 */
if (!defined('VERSION_NUMBER')) define('VERSION_NUMBER', LEED_VERSION_NUMBER);
if (!defined('VERSION_NAME')) define('VERSION_NAME', LEED_VERSION_NAME);

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

$theme = $configurationManager->get('theme');

//Instanciation du template
$tpl = new RainTPL();
//Definition des dossiers de template
raintpl::configure("base_url", null );
raintpl::configure("tpl_dir", './templates/'.$theme.'/' );
raintpl::configure("cache_dir", "./cache/tmp/" );

$resultUpdate = Update::ExecutePatch();

$userManager = new User();
$myUser = (isset($_SESSION['currentUser'])?unserialize($_SESSION['currentUser']):false);
if (empty($myUser)) {
    /* Pas d'utilisateur dans la session ?
     * On tente de récupérer une nouvelle session avec un jeton. */
    $myUser = User::existAuthToken();
    $_SESSION['currentUser'] = serialize($myUser);
}

$feedManager = new Feed();
$eventManager = new Event();
$folderManager = new Folder();

// Sélection de la langue de l'interface utilisateur
if (!$myUser) {
    $languages = Translation::getHttpAcceptLanguages();
} else {
    $languages = array($configurationManager->get('language'));
}

i18n_init($languages, dirname(__FILE__).'/templates/'.$theme.'/');
if ($resultUpdate) die (_t('LEED_UPDATE_MESSAGE'));

$view = '';
$tpl->assign('myUser',$myUser);
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
foreach(array_merge($_POST, $_GET) as $key => $val){
    if(is_string($val)) {
        $_[$key] = Functions::secure($val, 2); // on ne veut pas d'addslashes
    }
}

$tpl->assign('_',$_);
$tpl->assign('action','');

//Inclusion des plugins
Plugin::includeAll();
// pour inclure aussi les traductions des plugins dans les js
$tpl->assign('i18n_js',$i18n_js);

?>
