<?php

/*
 @nom: i18n
 @auteur: Idleman (idleman@idleman.fr)
 @description:  Classe de gestion de la traduction
 */

class I18n
{


	public static function init(){
		global $i18n,$i18n_js;
		if(!isset($i18n)){
			$i18n_js =  file_get_contents('locale/'.LANGAGE.'.json');
			$i18n = json_decode($i18n_js,true);
		}
	}	

	public static function t($key,$args=array(),$debug=false){
		global $i18n;

		$value = (isset($i18n[$key])?$i18n[$key]:'');
		for($i=0;$i<count($args);$i++){
			$value = str_replace('$'.($i+1), $args[$i], $value);
		}
		if($debug) var_dump($key,$args,$i18n[$key],$value);
		return $value;
	}

}
?>
