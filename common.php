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

function __autoload($class_name) {
    require_once ucfirst($class_name) . '.class.php';
}

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
?>
