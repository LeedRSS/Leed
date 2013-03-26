<?php
/*
@name Social
@author Mael <mael.illouz@gmail.com>
@link http://www.cobestran.com
@licence CC by nc sa http://creativecommons.org/licenses/by-nc-sa/2.0/fr/
@version 1.0.0
@description Le plugin Social permet de partager les news avec son réseau social préféré
*/
function Social_plugin_AddButton(&$link){
  echo ' < <a href="https://twitter.com/share?url='.$link->getLink().'" target="_blank">Tweet</a> 
    | <a href="http://www.facebook.com/share.php?u='.$link->getLink().'" target="_blank">Facebook</a>
    | <a href="https://plus.google.com/share?url='.$link->getLink().'hl=fr" target="_blank">Google+</a> >';
}

// Ajout de la fonction au Hook situé avant l'affichage des évenements
Plugin::addHook("event_post_top_options", "Social_plugin_AddButton");  
?>
