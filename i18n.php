<?php

/*
 @nom: i18n
 @auteur: Idleman (idleman@idleman.fr)
 @description:  Fonctions de gestion de la traduction
 */


function i18n_init(){
    global $i18n,$i18n_js;
    if(!isset($i18n)){
        $i18n_js =  file_get_contents(dirname(__FILE__).'/locale/'.LANGUAGE.'.json');
        $i18n = json_decode($i18n_js,true);
    }
}

function _t($key,$args=array(),$debug=false){
    global $i18n;

    $value = (isset($i18n[$key])?$i18n[$key]:'');
    for($i=0;$i<count($args);$i++){
        $value = str_replace('$'.($i+1), $args[$i], $value);
    }
    if($debug) var_dump($key,$args,$i18n[$key],$value);
    return $value;
}


?>
