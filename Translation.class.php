<?php

/*
 @description:  Classe de gestion des traductions
 */

class Translation 
{
	
	private $file;
	private $labels_var_name = 'labels';
	private $languages = array('fre-FR', 'eng-EN');
	const DEFAULT_LANG = 'fre-FR';
	
	public function __construct() {
		$this->file = 'templates/' . DEFAULT_THEME . '/translations.php';
	}
	
	public static function i18n($str, $lang = '') {
		static $me;
		
		if (!$lang) {
			$lang = self::DEFAULT_LANG;
		}
		if (!$me) {
			$me = new self();
		}
		$labels = $me->getTranslationArray();
		
		if (!isset($labels[$str]) || (isset($labels[$str]) && !isset($labels[$str][$lang])) ) {
			$me->setEntry($str, $lang);
		}
		
		if ($labels[$str][$lang] != '') {
			$translation = $labels[$str][$lang];
		} else {
			$translation = $labels[$str][self::DEFAULT_LANG];
		}
		if ($translation == '') {
			$translation = $str;
		}
		
		return $translation;
	}
	
	/* PRIVATE FUNCTIONS */
	
	private function getTranslationArray() {
		if (file_exists($this->file)) {
			include $this->file;
		}
		
		$labels_var_name = $this->labels_var_name;
		$labels = isset($$labels_var_name) ? $$labels_var_name : array();
		return $labels;
	}
	
	private function setEntry($str, $lang) {
		$labels = $this->getTranslationArray();
		if (!isset($labels[$str])) {
			$labels[$str] = array();
		}
		$labels[$str][$lang] = '';
		ksort($labels[$str]);
		ksort($labels);

		$this->writeFile($labels);		
	}
	
	private function writeFile($labels) {
		
		$content = '<?' . PHP_EOL;
		$content .= '$' . $this->labels_var_name . ' = array(' . "\n";
		foreach ($labels as $key => $translations) {
			$content .= "\t'" . $key . "' => array(";
			foreach ($translations as $lang => $translation) {
				$content .= "'" . $lang . "' => '" . str_replace("'", "\'", $translation) . "', ";
			}
			$content .= "),\n";
		}
		$content .= ');' . PHP_EOL;

		$return = file_put_contents($this->file, $content);
		if (!$return) {
			die('Veuillez vérifier les droits sur le fichier ' . $this->file);
		}
	}
}
?>
