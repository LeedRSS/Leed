<?php

/*
 @nom: Opml
 @auteur: Sbgodin (christophe.henry@sbgodin.fr)
 @description: Classe de gestion de l'import/export au format OPML
 */

require_once("common.php");
 
class Opml  {

	/**
	 * Met à jour les données des flux.
	 */
	function _update() {
		global $feedManager, $folderManager;
		$this->feeds = $feedManager->populate('name');
		$this->folders = $folderManager->loadAll(array('parent'=>-1),'name');
	}

	/**
	 * Convertit les caractères qui interfèrent avec le XML
	 */
	function _pourXml($string) {
		return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * Exporte récursivement les flux.
	 */
	function _export($folders, $niveauIndent=0) {
		$_ = ''; for($i=0;$i<$niveauIndent;$i++) $_.="\t";
		$xmlStream = '';
		foreach($folders as $folder) {
			// Pas utilisé, vu qu'il n'y a qu'un seul niveau de dossiers.
  			$xmlStream .= $this->_export($folder->getFolders(), $niveauIndent+1);
			$feeds = $folder->getFeeds();
			if (empty($feeds)) continue;
			$text = $this->_pourXml($folder->getName());
			$title = $this->_pourXml($folder->getName());
			$xmlStream .= "{$_}<outline text='$text' title='$title' icon=''>\n";
			foreach($feeds as $feed){
				$url = $this->_pourXml($feed->getUrl());
				$website = $this->_pourXml($feed->getWebsite());
				$title = $this->_pourXml($feed->getName());
				$text = $title;
				$description = $this->_pourXml($feed->getDescription());
				$xmlStream .= "{$_}{$_}<outline "
				."xmlUrl='$url' "
				."htmlUrl='$website' "
				."text='$text' "
				."title='$title' "
				."description='$description' "
				." />\n";
			}
			$xmlStream .= "{$_}</outline>\n";
		}
		return $xmlStream;
	}

	/**
	 * Exporte l'ensemble des flux et sort les en-têtes.
	 */
	function export() {
		$this->_update();
		$date = date('D, d M Y H:i:s O');
		$xmlStream = "<?xml version='1.0' encoding='utf-8'?>
<opml version='2.0'>
	<head>
		<title>Leed export</title>
		<ownerName>Leed</ownerName>
		<ownerEmail>idleman@idleman.fr</ownerEmail>
		<dateCreated>$date</dateCreated>
	</head>
	<body>\n";
		$xmlStream .= $this->_export($this->folders, 2);
		$xmlStream .= "\t</body>\n</opml>\n";
		return $xmlStream;
	}

	/**
	 * Importe les flux.
	 */
	function import() {
		throw new Exception('À faire.');
	}

}

?>
