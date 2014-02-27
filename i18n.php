<?php

/*
 @nom: i18n
 @auteur: Idleman (idleman@idleman.fr)
 @description:  Fonctions de gestion de la traduction
 */

class Translation {

    // Répertoire contenant les traductions
    const LOCALE_DIR = 'locale';

    /* Langue utilisée si aucune langue n'est demandée ou si les langues
     * demandées ne sont pas disponibles. Idem pour les traductions.*/
    const DEFAULT_LANGUAGE = 'fr';

    // tableau associatif des traductions
    var $trans = array();
    var $language = ''; // langue courante
    var $languages = array(); // langues disponibles

    /** @param location L'endroit où se trouve le dossier 'locale'
     *  @param languages Les langues demandées */
    function __construct($location, $languages=array()) {
        $this->location = $location;
        if (!is_array($languages)) $languages = array($languages);
        $this->listLanguages();
        $languages[]=self::DEFAULT_LANGUAGE;
        foreach ($languages as $language)
            if ($this->load($language)) {
                $this->language = $language;
                break;
            }
    }

    /* Peuple la liste des langues avec une traduction */
    protected function listLanguages() {
        $this->languages = array();
        $files = glob($this->location.'/'.self::LOCALE_DIR.'/*.json');
        if (is_array($files)) {
            foreach($files as $file){
                preg_match('/([a-z]{2})\.json$/', $file, $matches);
                assert('!empty($matches)');
                $this->languages [] = $matches[1];
            }
        }
    }

    /* Charge la traduction
     * @param language la langue sélectionnée
     * @return TRUE si le chargement s'est bien fait, FALSE sinon */
    protected function load($language) {
        if (!preg_match('/^[a-z]{2}$/', $language)) {
            error_log("Invalid language: '$language'");
            return false;
        }
        $trans = $this->loadFile($language);
        if (empty($trans)) return false;
        assert('in_array($language, $this->languages)');
        if ($language!=self::DEFAULT_LANGUAGE) {
            $defaultTrans = $this->loadFile(self::DEFAULT_LANGUAGE);
            assert('!empty($defaultTrans)');
            $trans = array_merge($defaultTrans, $trans);
        }
        $this->trans = $trans;
        return true;
    }

    /* Charge un fichier
     * @param $language Le fichier de langue concerné
     * @return Tableau associatif contenant les traductions */
    protected function loadFile($language) {
        $fileName = $this->location.'/'.self::LOCALE_DIR.'/'.$language.'.json';
        $content = @file_get_contents($fileName);
        if (empty($content)) {
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

    /* Ajoute une traduction à la suite de celle-ci.
     * Note : il faudra appeler getJson() si nécessaire */
    function append(Translation $other) {
        $this->trans = array_merge($this->trans, $other->trans);
    }

    /* @return la version Json des traductions */
    function getJson() {
        return json_encode($this->trans);
    }

}

// Initialise le singleton, avec les langues possibles
function i18n_init($languages){
    global $i18n,$i18n_js;
    if (!isset($i18n)) {
        $i18n = new Translation(dirname(__FILE__), $languages);
        $i18n_js = $i18n->getJson();
    }
    return $i18n->language;
}

// Appel rapide de la traduction
function _t($key,$args=array(),$debug=false){
    global $i18n;
    return $i18n->get($key, $args);
}


?>
