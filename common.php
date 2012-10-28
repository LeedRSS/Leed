<?php 

/*
 @nom: common
 @auteur: Idleman (idleman@idleman.fr)
 @description: Page incluse dans tous (ou presque) les fichiers du projet, inclus les entitées SQL et récupère/traite les variables de requetes
 */

session_start();
$start=microtime(true);
require_once('constant.php');
require_once('RainTPL.php');
class_exists('MysqlEntity') or require_once('MysqlEntity.class.php');
class_exists('Feed') or require_once('Feed.class.php');
class_exists('Event') or require_once('Event.class.php');
class_exists('Functions') or require_once('Functions.class.php');
class_exists('User') or require_once('User.class.php');
class_exists('Folder') or require_once('Folder.class.php');
class_exists('Configuration') or require_once('Configuration.class.php');

//error_reporting(E_ALL);

//Calage de la date
date_default_timezone_set('Europe/Paris'); 

$myUser = (isset($_SESSION['currentUser'])?unserialize($_SESSION['currentUser']):false);
$feedManager = new Feed();
$eventManager = new Event();
$userManager = new User();
$folderManager = new Folder();
$configurationManager = new Configuration();




$conf = $configurationManager->getAll();

//Instanciation du template
$tpl = new RainTPL();
//Definition des dossiers de template
raintpl::configure("base_url", null );
raintpl::configure("tpl_dir", './templates/'.DEFAULT_THEME.'/' );
raintpl::configure("cache_dir", "./cache/tmp/" );

$view = '';
$tpl->assign('myUser',$myUser);
$tpl->assign('feedManager',$feedManager);
$tpl->assign('eventManager',$eventManager);
$tpl->assign('userManager',$userManager);
$tpl->assign('folderManager',$folderManager);
$tpl->assign('configurationManager',$configurationManager);

//Récuperation et sécurisation de toutes les variables POST et GET
$_ = array();
foreach($_POST as $key=>$val){
$_[$key]=Functions::secure($val);
}
foreach($_GET as $key=>$val){
$_[$key]=Functions::secure($val);
}

$tpl->assign('_',$_);
?>