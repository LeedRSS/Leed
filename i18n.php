<?php

/*
 @nom: i18n
 @auteur: Idleman (http://blog.idleman.fr)
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
    var $translatedLanguages = array(); // langues traduites

    /** @param location L'endroit où se trouve le dossier 'locale'
     *  @param languages Les langues demandées */
    function __construct($location, $languages=array()) {
        $this->location = $location;
        if (!is_array($languages)) $languages = array($languages);
        $this->translatedLanguages = $this->listLanguages();
        $languages[]=self::DEFAULT_LANGUAGE;
        $this->languages = $languages;
        foreach ($languages as $language) {
            if (empty($language)) continue;
            if ($this->load($language)) {
                $this->language = $language;
                break;
            }
        }
    }

    /* @return la liste des langues avec une traduction */
    protected function listLanguages() {
        $translatedLanguages = array();
        $files = glob($this->location.'/'.self::LOCALE_DIR.'/*.json');
        if (is_array($files)) {
            foreach($files as $file){
                preg_match('/([a-z]{2})\.json$/', $file, $matches);
                $hasLocale = !empty($matches);
                assert($hasLocale);
                $translatedLanguages [] = $matches[1];
            }
        }
        return $translatedLanguages;
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
        $isLanguageKnown = in_array($language, $this->translatedLanguages);
        assert($isLanguageKnown);
        if ($language!=self::DEFAULT_LANGUAGE) {
            $defaultTrans = $this->loadFile(self::DEFAULT_LANGUAGE);
            $hasDefaultTrans = !empty($defaultTrans);
            assert($hasDefaultTrans);
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
            foreach ($translations as $id => $translation) {
                if (empty($translation)) {
                    # Retire les traductions vides afin qu'elles soient
                    # traduites dans une autre langue si possible.
                    unset($translations[$id]);
                }
            }

            if (!empty($content) && empty($translations))
                error_log("Error while loading '$fileName'");
        }
        return $translations;
    }

    /* Retourne la traduction et substitue les variables.
     * get('TEST_TRANS', array('4'))
     * Retournera 'Nombre : 4' si TEST_TRANS == 'Nombre : $1' */
    function get($key, $args=array()) {
        if (isset($this->trans[$key])&&!empty($this->trans[$key])) {
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

    /* @return un tableau des langues préférées */
    static function getHttpAcceptLanguages() {
        /** Exemple de directive :
         * eo,fr;q=0.8,fr-FR;q=0.6,en-US;q=0.4,en;q=0.2
         * Les langues sont séparées entre elles par des virgules.
         * Chaque langue est séparée du coefficient, si présent, par un point-virgule.
         */
        $httpAcceptLanguage = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ?
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] : self::DEFAULT_LANGUAGE;
        $languageList = array();
        foreach (explode(',', $httpAcceptLanguage) as $language) {
            $languageList[] = substr($language, 0, 2); // fr-FR;q=0.6 --> fr
        }
        return array_unique($languageList); // en-US,en-UK --> en, en --> en
    }

}

// Initialise le singleton, avec les langues possibles
function i18n_init($languages, $location){
    global $i18n,$i18n_js;
    if (!isset($i18n)) {
        // Charge d'abord les traductions de base
        $i18n = new Translation(dirname(__FILE__), $languages);
        // Charge ensuite la traduction demandée (celle du thème courant)
        $i18n->append(new Translation($location, $languages));
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
