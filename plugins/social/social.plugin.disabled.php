<?php
/*
@name Social
@author Cobalt74 <cobalt74@gmail.com>
@link http://www.cobestran.com
@licence CC by nc sa http://creativecommons.org/licenses/by-nc-sa/2.0/fr/
@version 1.0.0
@description Le plugin Social permet de partager les news avec son réseau social préféré
*/
function Social_plugin_AddButton(&$link){
  echo '<div class="social_sep">
        <div onclick="openURL(\'https://twitter.com/share?url='.$link->getLink().'\');" class="social_div">Tweeter</div> 
        <div onclick="openURL(\'http://www.facebook.com/share.php?u='.$link->getLink().'\');" class="social_div">Facebook</div> 
        <div onclick="openURL(\'https://plus.google.com/share?url='.$link->getLink().'&hl=fr\');" class="social_div">Google+</div>
        </div>';
}

// Ajout de la fonction au Hook situé avant l'affichage des évenements
Plugin::addCss("/css/style.css"); 
Plugin::addJs("/js/main.js");
Plugin::addHook("event_post_top_options", "Social_plugin_AddButton"); 
Plugin::addHook("event_pre_bottom_options", "Social_plugin_AddButton");
?>
