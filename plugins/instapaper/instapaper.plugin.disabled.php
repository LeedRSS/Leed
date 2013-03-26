<?php
/*
@name Instaleed
@author Idleman <idleman@idleman.fr>
@link http://blog.idleman.fr
@licence CC by nc sa http://creativecommons.org/licenses/by-nc-sa/2.0/fr/
@version 1.0.0
@description Le plugin InstaPaper permet d'afficher les évenements directement sur instapaper lors du clic sur le titre d'un évenement
*/
function instapaper_plugin_updateTitle(&$events){
	foreach($events as $event){
		$event->setLink('http://www.instapaper.com/text?u='.$event->getLink());
	}
}

// Ajout de la fonction au Hook situé avant l'affichage des évenements
Plugin::addHook("index_post_treatment", "instapaper_plugin_updateTitle");  
?>