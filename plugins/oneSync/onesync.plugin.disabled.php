<?php
/*
@name OneSync
@author Idleman <idleman@idleman.fr>
@link http://blog.idleman.fr
@licence CC by nc sa http://creativecommons.org/licenses/by-nc-sa/2.0/fr/
@version 1.0.0
@description Le plugin OneSync ajout un bouton à coté de chaque flux afin de synchroniser uniquement ce flux
*/

function OneSync_plugin_AddButton(&$feed){
	echo '<span class="pointer onsyncButton" onclick="onesync_validate(\''.$feed['id'].'\');" alt="Synchroniser" title="Synchroniser">↺</span> ';
}

function OneSync_plugin_SynchronyzeOne(&$_){
	if ($_['action']=='syncronyzeOne'){
		$myUser = (isset($_SESSION['currentUser'])?unserialize($_SESSION['currentUser']):false);
		if($myUser==false) exit('Vous devez vous connecter pour cette action.');
			if(isset($_['feed']) && $_['feed']!=''){
				$feedManager = new Feed();
				$feed = $feedManager->getById($_['feed']);
				$feed->parse();
			}
			header('location: ./index.php');
		}
		
	}

Plugin::addCss("/css/style.css"); 
Plugin::addJs("/js/main.js"); 
// Ajout de la fonction au Hook situé avant l'affichage des liens de flux
Plugin::addHook("menu_post_feed_link", "OneSync_plugin_AddButton");  
Plugin::addHook("action_post_case", "OneSync_plugin_SynchronyzeOne"); 
?>