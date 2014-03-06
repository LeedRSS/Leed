<?php

/*
 @nom: Plugin
 @auteur: Valentin CARRUESCO (idleman@idleman.fr)
 @description: Classe de gestion des plugins au travers de l'application
 */

class Plugin{

    const FOLDER = '/plugins';
    protected $name,$author,$mail,$link,$licence,$path,$description,$version,$state,$type;

    function __construct(){
    }

    public static function includeAll(){
        global $i18n, $i18n_js;
        $pluginFiles = Plugin::getFiles(true);
        if(is_array($pluginFiles)) {
            foreach($pluginFiles as $pluginFile) {
                // Chargement du fichier de Langue du plugin
                $i18n->append(new Translation(dirname($pluginFile)));
                // Inclusion du coeur de plugin
                include $pluginFile;
                // Gestion des css du plugin en fonction du thème actif
                $cssTheme = glob(dirname($pluginFile).'/*/'.DEFAULT_THEME.'.css');
                $cssDefault = glob(dirname($pluginFile).'/*/default.css');
                if(isset($cssTheme[0])){
                    $GLOBALS['hooks']['css_files'][] = Functions::relativePath(str_replace('\\','/',dirname(__FILE__)),str_replace('\\','/',$cssTheme[0]));
                }else if(isset($cssDefault[0])){
                    $GLOBALS['hooks']['css_files'][] =  Functions::relativePath(str_replace('\\','/',dirname(__FILE__)),str_replace('\\','/',$cssDefault[0]));
                }
            }
        }
        $i18n_js = $i18n->getJson();
    }

    private static function getStates(){
        $stateFile = dirname(__FILE__).Plugin::FOLDER.'/plugins.states.json';
        if(!file_exists($stateFile)) touch($stateFile);
        return json_decode(file_get_contents($stateFile),true);
    }
    private static function setStates($states){
        $stateFile = dirname(__FILE__).Plugin::FOLDER.'/plugins.states.json';
        file_put_contents($stateFile,json_encode($states));
    }
    public static function pruneStates() {
        $statesBefore = self::getStates();
        if(empty($statesBefore))
            $statesBefore = array();

        $statesAfter = array();
        $error = false;
        if (is_array($statesBefore))
        {
            foreach($statesBefore as $file=>$state) {
                if (file_exists($file))
                    $statesAfter[$file] = $state;
                else
                    $error = true;
            }
        }
        if ($error) self::setStates($statesAfter);
    }


    private static function getObject($pluginFile){
        $plugin = new Plugin();
        $fileLines = file_get_contents($pluginFile);

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

        if(preg_match("#@type\s(.+)[\r\n]#", $fileLines, $match))
            $plugin->setType(trim($match[1]));

        if(preg_match("#@description\s(.+)[\r\n]#", $fileLines, $match))
            $plugin->setDescription(trim($match[1]));

        if(Plugin::loadState($pluginFile) || $plugin->getType()=='component'){
            $plugin->setState(1);
        }else{
            $plugin->setState(0);
        }
        $plugin->setPath($pluginFile);
        return $plugin;
    }

    public static function getAll(){
        $pluginFiles = Plugin::getFiles();

        $plugins = array();
        if(is_array($pluginFiles)) {
            foreach($pluginFiles as $pluginFile) {
                $plugin = Plugin::getObject($pluginFile);
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
        $pathInfo = explode('/',dirname($bt[0]['file']));
        $count = count($pathInfo);
        $name = $pathInfo[$count-1];
        $path =  '.'.Plugin::FOLDER.'/'.$name.$css;

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

    public static function addLink($rel, $link) {
        $GLOBALS['hooks']['head_link'][] = array("rel"=>$rel, "link"=>$link);
    }

    public static function callLink(){
        $return='';
        if(isset($GLOBALS['hooks']['head_link'])) {
            foreach($GLOBALS['hooks']['head_link'] as $head_link) {
                $return .='<link rel="'.$head_link['rel'].'" href="'.$head_link['link'].'" />'."\n";
            }
        }
        return $return;
    }

    public static function path(){
        $bt =  debug_backtrace();
        $pathInfo = explode('/',dirname($bt[0]['file']));
        $count = count($pathInfo);
        $name = $pathInfo[$count-1];
        return '.'.Plugin::FOLDER.'/'.$name.'/';
    }

    public static function addJs($js) {
        $bt =  debug_backtrace();
        $pathInfo = explode('/',dirname($bt[0]['file']));
        $count = count($pathInfo);
        $name = $pathInfo[$count-1];
        $path = '.'.Plugin::FOLDER.'/'.$name.$js;

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

        $enabled = $disabled =  array();
        $files = glob(dirname(__FILE__). Plugin::FOLDER .'/*/*.plugin*.php');
        if(empty($files))
            $files = array();

        foreach($files as $file){
            $plugin = Plugin::getObject($file);
            if($plugin->getState()){
                $enabled [] =  $file;
            }else{
                $disabled [] =  $file;
            }
        }
        if(!$onlyActivated)$enabled = array_merge($enabled,$disabled);
        return $enabled;
    }


    public static function loadState($plugin){
        $states = Plugin::getStates();
        return (isset($states[$plugin])?$states[$plugin]:false);
    }

    public static function changeState($plugin,$state){
        $states = Plugin::getStates();
        $states[$plugin] = $state;

        Plugin::setStates($states);
    }


    public static function enabled($pluginUid){
        $plugins = Plugin::getAll();

        foreach($plugins as $plugin){
            if($plugin->getUid()==$pluginUid){
                Plugin::changeState($plugin->getPath(),true);
                $install = dirname($plugin->getPath()).'/install.php';
                if(file_exists($install))require_once($install);
            }
        }
    }
    public static function disabled($pluginUid){
        $plugins = Plugin::getAll();
        foreach($plugins as $plugin){
            if($plugin->getUid()==$pluginUid){
                Plugin::changeState($plugin->getPath(),false);
                $uninstall = dirname($plugin->getPath()).'/uninstall.php';
                if(file_exists($uninstall))require_once($uninstall);
            }
        }

    }

    function getUid(){
        $pathInfo = explode('/',$this->getPath());
        $count = count($pathInfo);
        $name = $pathInfo[$count-1];
        return $pathInfo[$count -2].'-'.substr($name,0,strpos($name,'.'));
    }


    static function sortPlugin($a, $b){
        if ($a->getName() == $b->getName())
        return 0;
        return ($a->getName() < $b->getName()) ? -1 : 1;
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

    function getType(){
        return $this->type;
    }

    function setType($type){
        $this->type = $type;
    }

}

?>
