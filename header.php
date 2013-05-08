<?php 
if(!file_exists('constant.php'))header('location: install.php');
require_once('common.php'); 

$tpl->assign('VERSION_NUMBER',$_SESSION['version_number']);
$tpl->assign('VERSION_NAME',$_SESSION['version_name']);

?>