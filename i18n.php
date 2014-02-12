<?php

/*
 @nom: i18n
 @auteur: Idleman (idleman@idleman.fr)
 @description:  Fonctions de gestion de la traduction
 */

define('DEFAULT_LANGUAGE', 'fr');

class Translation {

    const LOCALE_DIR='locale';

    // tableau associatif des traductions
    var $trans = array();

    function __construct($location,$p_language=null) {
        (defined('LANGUAGE')?$this->language = LANGUAGE:$this->language=$p_language);
        $this->defaultLanguage = DEFAULT_LANGUAGE;
        $this->location = $location;
        $this->load();
    }

    /* Charge la traduction pour la langue sélectionnée.*/
    protected function load() {
        $trans = $this->loadFile($this->language);
        if ($this->language!=$this->defaultLanguage) {
            $defaultTrans = $this->loadFile($this->defaultLanguage);
            $trans = array_merge($defaultTrans, $trans);
        }
        $this->trans = $trans;
    }

    /* Charge un fichier
     * @param $language Le fichier de langue concerné
     * @return Tableau associatif contenant les traductions */
    protected function loadFile($language) {
        $fileName = $this->location.'/'.self::LOCALE_DIR.'/'.$language.'.json';
        $content = @file_get_contents($fileName);
        if (empty($content)) {
            error_log("Translation for $language ($fileName) not found!");
            $translations = array();
        } else {
            $translations = json_decode($content, true);
        }
        return $translations;
    }

    /* Retourne la traduction et substitue les variables.
     * get('TEST_TRANS', array('4'))
     * Retournera 'Nombre : 4' si TEST_TRANS == 'Nombre : $1' */
    function get($key, $args=array()) {
        if (isset($this->trans[$key])) {
            $value = $this->trans[$key];
            for($i=0;$i<count($args);$i++){
                $value = str_replace('$'.($i+1), $args[$i], $value);
            }
        } else {
            $value = $key;
        }
        return $value;
    }

    /* Ajoute une traduction à la suite de celle-ci. */
    function append(Translation $other) {
        $this->trans = array_merge($this->trans, $other->trans);
    }

    function getJson() {
        return json_encode($this->trans);
    }

}

function i18n_init($language=null){
    global $i18n,$i18n_js;
    if(!isset($i18n)){
        $i18n = new Translation(dirname(__FILE__),$language);
        $i18n_js = $i18n->getJson();
    }
}

function _t($key,$args=array(),$debug=false){
    global $i18n;
    return $i18n->get($key, $args);
}


?>
