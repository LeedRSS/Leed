<?php
/*
@name Fleedicon
@author Idleman <idleman@idleman.fr>
@link http://blog.idleman.fr
@licence CC by nc sa http://creativecommons.org/licenses/by-nc-sa/2.0/fr/
@version 1.0.0
@description Le plugin Fleedicon ajoute un favicon à gauche de chaque flux
*/

function fleedicon_plugin_AddFavicon(&$feed){
	if(!file_exists(Plugin::path().'favicons/'))mkdir(Plugin::path().'favicons/');
	$iconPath = Plugin::path().'favicons/'.$feed['id'].'.png';
	if(!file_exists($iconPath)){
		$url = $feed['url'];
		if (strpos($url, 'http://')!==false) $url.= 'http://'.$url;
		if(preg_match("#http(s)?\:\/\/([a-zA-Z-0-9\.\-]+)\/#", $url, $match)){
			file_put_contents($iconPath,file_get_contents('http://g.etfv.co/'.'http'.$match[1].'://'.$match[2]),FILE_APPEND);
		}
	}

	echo '<div class="favicon"><img src="'.$iconPath.'"/></div>';
}



Plugin::addCss("/css/style.css"); 
// Ajout de la fonction au Hook situé avant l'affichage des liens de flux
Plugin::addHook("menu_pre_feed_link", "fleedicon_plugin_AddFavicon");  

?>