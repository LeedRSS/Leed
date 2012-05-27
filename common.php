<?php session_start();
$start=microtime(true);
require_once('SQLiteEntity.class.php');
require_once('Feed.class.php');
require_once('Event.class.php');
require_once('Functions.class.php');
require_once('User.class.php');
require_once('Folder.class.php');
require_once('Configuration.class.php');

//Calage de la date
date_default_timezone_set('Europe/Paris'); 

$myUser = (isset($_SESSION['currentUser'])?unserialize($_SESSION['currentUser']):false);
$feedManager = new Feed();
$eventManager = new Event();
$userManager = new User();
$folderManager = new Folder();
$configurationManager = new Configuration();

$conf = $configurationManager->getAll();

//Récuperation et sécurisation de toutes les variables POST et GET
$_ = array();
foreach($_POST as $key=>$val){
$_[$key]=Functions::secure($val);
}
foreach($_GET as $key=>$val){
$_[$key]=Functions::secure($val);
}
?>