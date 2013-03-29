<?php

/*
 @nom: Plugin
 @auteur: Idleman (idleman@idleman.fr)
 @description: Classe de gestion des plugins au travers de l'application
 */

class Plugin{
	const FOLDER = '/plugins';
	protected $name,$author,$mail,$link,$licence,$path,$description,$version,$state;

	function __construct(){
		
	}

	public static function includeAll(){
		$pluginFiles = Plugin::getFiles(true);
		if(is_array($pluginFiles)) {   
			foreach($pluginFiles as $pluginFile) {  
				include $pluginFile;  
			}  
		}  
	}

	public static function getAll(){
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
			    
			    if(preg_match("#@name\s(.+)[\r\n]#", $fileLines, $match))
			     	$plugin->setName($match[1]);
			    
			    if(preg_match("#@licence\s(.+)[\r\n]#", $fileLines, $match))
			     	$plugin->setLicence($match[1]);
			    
			    if(preg_match("#@version\s(.+)[\r\n]#", $fileLines, $match))
			     	$plugin->setVersion($match[1]);
			    
			    if(preg_match("#@link\s(.+)[\r\n]#", $fileLines, $match))
			     	$plugin->setLink(trim($match[1]));
			    
			    if(preg_match("#@description\s(.+)[\r\n]#", $fileLines, $match))
			     	$plugin->setDescription(trim($match[1]));
			     
			    if(strpos($pluginFile,'.plugin.enabled.php')!==false){
			    	$plugin->setState(1);
			    }else{
			    	$plugin->setState(0);
			    }
			    $plugin->setPath($pluginFile);
				$plugins[]=$plugin;
			}  
		}

		usort($plugins, "Plugin::sortPlugin");

		return $plugins;
	}

	

		public static function addHook($hookName, $functionName) {  
		    $GLOBALS['hooks'][$hookName][] = $functionName;  
		} 

		public static function addCss($css) {  
			$bt =  debug_backtrace();
			$path = Functions::relativePath(str_replace('\\','/',dirname(__FILE__)),str_replace('\\','/',dirname($bt[0]['file']).$css));
		    $GLOBALS['hooks']['css_files'][] = $path;  
		}

		public static function callCss(){
			$return='';
		    if(isset($GLOBALS['hooks']['css_files'])) { 
		        foreach($GLOBALS['hooks']['css_files'] as $css_file) {  
		            $return .='<link rel="stylesheet" href="'.$css_file.'">'."\n";
		        }  
		    }    
		    return $return;
		}


		public static function path(){
			$bt =  debug_backtrace();
			return Functions::relativePath(str_replace('\\','/',dirname(__FILE__)),str_replace('\\','/',dirname($bt[0]['file']))).'/'; 
		}

		public static function addJs($js) {  
			$bt =  debug_backtrace();
			$path = Functions::relativePath(str_replace('\\','/',dirname(__FILE__)),str_replace('\\','/',dirname($bt[0]['file']).$js));
		    $GLOBALS['hooks']['js_files'][] = $path;  
		}

		public static function callJs(){
			$return='';
		    if(isset($GLOBALS['hooks']['js_files'])) { 
		        foreach($GLOBALS['hooks']['js_files'] as $js_file) {  
		            $return .='<script type="text/javascript" src="'.$js_file.'"></script>'."\n";
		        }  
		    }    
		    return $return;
		}

		public static function callHook($hookName, $hookArguments) {  
			//echo '<div style="display:inline;background-color:#CC47CB;padding:3px;border:5px solid #9F1A9E;border-radius:5px;color:#ffffff;font-size:15px;">'.$hookName.'</div>';
		    if(isset($GLOBALS['hooks'][$hookName])) { 
		        foreach($GLOBALS['hooks'][$hookName] as $functionName) {  
		            call_user_func_array($functionName, $hookArguments);  
		        }  
		    }  
		} 

	public static function getFiles($onlyActivated=false){
		$files = glob(dirname(__FILE__) . Plugin::FOLDER .'/*/*.plugin.enabled.php');
		if (!is_array($files)) $files = array();
		if(!$onlyActivated)$files = array_merge($files,glob(dirname(__FILE__) . Plugin::FOLDER .'/*/*.plugin.disabled.php'));
		return $files;
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

	function getState(){
		return $this->state;
	}

	function setState($state){
		$this->state = $state;
	}

	function getUid(){
		$pathInfo = explode('/',$this->getPath()); 
		$count = count($pathInfo);
		$name = $pathInfo[$count-1];
		return $pathInfo[$count -2].'-'.substr($name,0,strpos($name,'.'));
	}

	public static function enabled($pluginUid){
		$plugins = Plugin::getAll();

		foreach($plugins as $plugin){
			if($plugin->getUid()==$pluginUid){
				rename($plugin->getPath(),str_replace('.plugin.disabled.php', '.plugin.enabled.php', $plugin->getPath()));
				$install = dirname($plugin->getPath()).'/install.php';
				if(file_exists($install))require_once($install);
			}
		}
		
	}
	public static function disabled($pluginUid){
		$plugins = Plugin::getAll();
		foreach($plugins as $plugin){
			if($plugin->getUid()==$pluginUid){
				rename($plugin->getPath(),str_replace('.plugin.enabled.php', '.plugin.disabled.php', $plugin->getPath()));
				$uninstall = dirname($plugin->getPath()).'/uninstall.php';
				if(file_exists($uninstall))require_once($uninstall);
			}
		}
		
	}

	static function sortPlugin($a, $b){
		if ($a->getName() == $b->getName()) 
        return 0;
	    
	    return ($a->getName() < $b->getName()) ? -1 : 1;
	}


}

?>
