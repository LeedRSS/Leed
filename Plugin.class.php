<?php

/*
 @nom: Plugin
 @auteur: Idleman (idleman@idleman.fr)
 @description: Classe de gestion des plugins au travers de l'application
 */

class Plugin{
	const FOLDER = '/plugins';
	protected $name,$author,$mail,$link,$licence,$path,$description,$version;

	function __construct(){
		
	}

	static function includeAll(){
		$pluginFiles = Plugin::getFiles();
		if(is_array($pluginFiles)) {   
			foreach($pluginFiles as $pluginFile) {  
				include $pluginFile;  
			}  
		}  
	}

	static function getAll(){
		$pluginFiles = Plugin::getFiles(); 
		$plugins = array();
		if(is_array($pluginFiles)) {   
			foreach($pluginFiles as $pluginFile) {  
				$plugin = new Plugin();
				$fileLines = file_get_contents($pluginFile);
				//Author
			    if(preg_match("#@author\s(.+)\s\<#", $fileLines, $match))
					$plugin->setAuthor(trim($match[1]));
			    
			    if(preg_match("#@author\s(.+)\s\<([a-z\@\.A-Z\s\-]+)\>#", $fileLines, $match))
					$plugin->setMail(strtolower($match[2]));
			    
			    if(preg_match("#@name\s(.+)\r#", $fileLines, $match))
			     	$plugin->setName($match[1]);
			    
			    if(preg_match("#@licence\s(.+)\r#", $fileLines, $match))
			     	$plugin->setLicence($match[1]);
			    
			    if(preg_match("#@version\s(.+)\r#", $fileLines, $match))
			     	$plugin->setVersion($match[1]);
			    
			    if(preg_match("#@link\s(.+)\r#", $fileLines, $match))
			     	$plugin->setLink(trim($match[1]));
			    
			    if(preg_match("#@description\s(.+)\r#", $fileLines, $match))
			     	$plugin->setDescription(trim($match[1]));
			     

				$plugins[]=$plugin;
			}  
		}
		return $plugins;
	}


		function addHook($hookName, $functionName) {  
		    $GLOBALS['hooks'][$hookName][] = $functionName;  
		} 

		function addCss($css) {  
			$bt =  debug_backtrace();
			$path = Functions::relativePath(str_replace('\\','/',dirname(__FILE__)),str_replace('\\','/',dirname($bt[0]['file']).$css));
		    $GLOBALS['hooks']['css_files'][] = $path;  
		}

		function callCss() {  
			$return='';
		    if(isset($GLOBALS['hooks']['css_files'])) { 
		        foreach($GLOBALS['hooks']['css_files'] as $css_file) {  
		            $return .='<link rel="stylesheet" href="'.$css_file.'">'."\n";
		        }  
		    }    
		    return $return;
		}

		function addJs($js) {  
			$bt =  debug_backtrace();
			$path = Functions::relativePath(str_replace('\\','/',dirname(__FILE__)),str_replace('\\','/',dirname($bt[0]['file']).$js));
		    $GLOBALS['hooks']['js_files'][] = $path;  
		}

		function callJs() {  
			$return='';
		    if(isset($GLOBALS['hooks']['js_files'])) { 
		        foreach($GLOBALS['hooks']['js_files'] as $js_file) {  
		            $return .='<script type="text/javascript" src="'.$js_file.'"></script>'."\n";
		        }  
		    }    
		    return $return;
		}

		function callHook($hookName, $hookArguments) {  
		    if(isset($GLOBALS['hooks'][$hookName])) { 
		        foreach($GLOBALS['hooks'][$hookName] as $functionName) {  
		            call_user_func_array($functionName, $hookArguments);  
		        }  
		    }  
		} 

	static function getFiles(){
		return glob(dirname(__FILE__) . Plugin::FOLDER .'/*/*.plugin.php');
	}

	function getName(){
		return $this->name;
	}

	function setName($name){
		$this->name = $name;
	}

	function setAuthor($author){
		$this->author = $author;
	}

	function getAuthor(){
		return $this->author;
	}

	function getMail(){
		return $this->mail;
	}

	function setMail($mail){
		$this->mail = $mail;
	}

	function getLicence(){
		return $this->licence;
	}

	function setLicence($licence){
		$this->licence = $licence;
	}

	function getPath(){
		return $this->path;
	}

	function setPath($path){
		$this->path = $path;
	}

	function getDescription(){
		return $this->description;
	}

	function setDescription($description){
		$this->description = $description;
	}


	function getLink(){
		return $this->link;
	}

	function setLink($link){
		$this->link = $link;
	}

	function getVersion(){
		return $this->version;
	}

	function setVersion($version){
		$this->version = $version;
	}



}

?>
