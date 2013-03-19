<?php

/*
 @nom: Opml
 @auteur: Sbgodin (christophe.henry@sbgodin.fr)
 @description: Classe de gestion de l'import/export au format OPML
 */

require_once("common.php");
 
class Opml  {

	// liens déjà connus, déjà abonnés, au moment de l'importation
	public $alreadyKnowns = array();

	/**
	 * Met à jour les données des flux.
	 */
	protected function update() {
		global $feedManager, $folderManager;
		$this->feeds = $feedManager->populate('name');
		$this->folders = $folderManager->loadAll(array('parent'=>-1),'name');
	}

	/**
	 * Convertit les caractères qui interfèrent avec le XML
	 */
	protected function escapeXml($string) {
		return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * Exporte récursivement les flux.
	 */
	protected function exportRecursive($folders, $identLevel=0) {
		$_ = ''; for($i=0;$i<$identLevel;$i++) $_.="\t";
		$xmlStream = '';
		foreach($folders as $folder) {
			// Pas utilisé, vu qu'il n'y a qu'un seul niveau de dossiers.
  			$xmlStream .= $this->exportRecursive(
				$folder->getFolders(), $identLevel+1
			);
			$feeds = $folder->getFeeds();
			if (empty($feeds)) continue;
			$text = $this->escapeXml($folder->getName());
			$title = $this->escapeXml($folder->getName());
			$xmlStream .= "{$_}<outline text='$text' title='$title' icon=''>\n";
			foreach($feeds as $feed){
				$url = $this->escapeXml($feed->getUrl());
				$website = $this->escapeXml($feed->getWebsite());
				$title = $this->escapeXml($feed->getName());
				$text = $title;
				$description = $this->escapeXml($feed->getDescription());
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
		$this->update();
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
		$xmlStream .= $this->exportRecursive($this->folders, 2);
		$xmlStream .= "\t</body>\n</opml>\n";
		return $xmlStream;
	}

	protected function importRec($folder, $folderId=1){
		$folderManager = new Folder();
		$feedManager = new Feed();
		foreach($folder as $item) {
			// Cela varie selon les implémentations d'OPML.
			$feedName = $item['text'] ? 'text' : 'title';
			if (isset($item->outline[0])) { // un dossier
				$folder = $folderManager->load(array('name'=>$item[$feedName]));
				$folder = (!$folder?new Folder():$folder);
				$folder->setName($item[$feedName]);
				$folder->setParent(($folderId==1?-1:$folderId));
				$folder->setIsopen(0);
				if($folder->getId()=='') $folder->save();
				$this->importRec($item->outline,$folder->getId());
			} else { // un flux
				$newFeed = $feedManager->load(array('url'=>$item[0]['xmlUrl']));
				$newFeed = (!$newFeed?new Feed():$newFeed);
				if($newFeed->getId()=='') {
					/* Ne télécharge pas à nouveau le même lien, même s'il est
					   dans un autre dossier. */
					$newFeed->setName($item[0][$feedName]);
					$newFeed->setUrl($item[0]['xmlUrl']);
					$newFeed->setDescription($item[0]['description']);
					$newFeed->setWebsite($item[0]['htmlUrl']);
					$newFeed->setFolder($folderId);
					$newFeed->save();
					// $newFeed->parse();
				} else {
					$this->alreadyKnowns[]= (object) array(
						'description' => $item[0]['description'],
						'xmlUrl' => $item[0]['xmlUrl']
					);
				}
			}
		}
	}
	
	/**
	 * Importe les flux.
	 */
	function import() {
		require_once("SimplePie.class.php");
		$file = $_FILES['newImport']['tmp_name'];
		$internalErrors = libxml_use_internal_errors(true);
		$xml = @simplexml_load_file($file);
		$errorOutput = array();
		foreach (libxml_get_errors() as $error) {
			$errorOutput []= "{$error->message} (line {$error->line})";
		}
		libxml_clear_errors();
		libxml_use_internal_errors($internalErrors);
		if (!empty($xml) && empty($errorOutput)) {
			$this->importRec($xml->body->outline);
		}
		return $errorOutput;
	}

}

?>
