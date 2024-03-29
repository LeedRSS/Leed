<?php

/*
 @nom: Plugin
 @auteur: Valentin CARRUESCO (http://blog.idleman.fr)
 @description: Classe de gestion des plugins au travers de l'application
 */

class Plugin{

    const FOLDER = '/plugins';
    protected $name,$author,$address,$link,$licence,$path,$description,$version,$state,$type;

    function __construct(){
    }

    public static function includeAll(){
        global $i18n, $i18n_js, $theme;
        $pluginFiles = Plugin::getFiles(true);
        if(is_array($pluginFiles)) {
            foreach($pluginFiles as $pluginFile) {
                // Chargement du fichier de Langue du plugin
                $i18n->append(new Translation(dirname($pluginFile),$i18n->languages));
                // Inclusion du coeur de plugin
                include $pluginFile;
                // Gestion des css du plugin en fonction du thème actif
                $cssTheme = glob(dirname($pluginFile).'/*/'.$theme.'.css');
                $cssDefault = glob(dirname($pluginFile).'/*/default.css');
                if(isset($cssTheme[0])){
                    $GLOBALS['hooks']['css_files'][] = Functions::relativePath(str_replace('\\','/',dirname(__FILE__)),str_replace('\\','/',$cssTheme[0]));
                }else if(isset($cssDefault[0])){
                    $GLOBALS['hooks']['css_files'][] = Functions::relativePath(str_replace('\\','/',dirname(__FILE__)),str_replace('\\','/',$cssDefault[0]));
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

        if(preg_match_all("#@author\s(.+)\s\<(.*)\>#", $fileLines, $matches)) {
            foreach($matches[1] as $match) {
                $authors[] = trim($match);
            }
            $plugin->setAuthor($authors);

            foreach($matches[2] as $match) {
                $address[] = strtolower($match);
            }
            $plugin->setAddress($address);
        }

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

    public static function install($url) {
        $logger = new Logger('settings');
        if(empty($url)){
            $logger->appendLogs("Pas d'url renseignée.");
        }elseif(filter_var($url, FILTER_VALIDATE_URL) === false){
            $logger->appendLogs("L'url renseignée n'est pas valide.");
        }else{
            $logger->appendLogs('Téléchargement du plugin...');
            $pluginBaseFolder = str_replace('/', '', self::FOLDER).'/';
            $tempZipName = $pluginBaseFolder.md5(microtime());
            file_put_contents($tempZipName,self::getUrlContent(urldecode($url)));
            if(file_exists($tempZipName)){
                $logger->appendLogs('Plugin téléchargé <span class="button notice">OK</span>');
                $logger->appendLogs('Extraction du plugin...');
                $zip = new ZipArchive;
                $res = $zip->open($tempZipName);
                if ($res === TRUE) {
                    $tempZipFolder = $tempZipName.'_';
                    $pluginFolder = $tempZipFolder;
                    $zip->extractTo($tempZipFolder);
                    $zip->close();
                    $logger->appendLogs('Plugin extrait <span class="button notice">OK</span>');
                    $fi = new FilesystemIterator($tempZipFolder, FilesystemIterator::SKIP_DOTS);
                    if(iterator_count($fi) === 1) {
                        foreach($fi as $file){
                            $filename = $file->getFilename();
                            $pluginFolder = $pluginBaseFolder.$filename;
                            rename($tempZipFolder.'/'.$filename, $pluginFolder);
                            rmdir($tempZipFolder);
                        }
                    }
                    $pluginName = glob($pluginFolder.'/*.plugin*.php');
                    if(count($pluginName)>0){
                        $pluginName = str_replace(array($pluginFolder.'/','.enabled','.disabled','.plugin','.php'),'',$pluginName[0]);
                        if(!file_exists($pluginBaseFolder.$pluginName)){
                            $logger->appendLogs('Renommage...');
                            if(rename($pluginFolder,$pluginBaseFolder.$pluginName)){
                                $logger->appendLogs('Plugin installé, rechargez la page pour voir le plugin <span class="button notice">pensez à l\'activer</span>');
                            }else{
                                Functions::rmFullDir($pluginFolder);
                                $logger->appendLogs('Impossible de renommer le plugin <span class="button error">Erreur</span>');
                            }
                        }else{
                            $logger->appendLogs('Plugin déjà installé <span class="button warning">OK</span>');
                        }
                    }else{
                        $logger->appendLogs('Plugin invalide, fichier principal manquant <span class="button error">Erreur</span>');
                    }

                } else {
                    $logger->appendLogs('Echec de l\'extraction <span class="button error">Erreur</span>');
                }
                unlink($tempZipName);
            }else{
                $logger->appendLogs('Echec du téléchargement <span class="button error">Erreur</span>');
            }
        }
        if(Functions::isAjaxCall()){
            echo json_encode($logger->getLogs(), JSON_HEX_QUOT | JSON_HEX_TAG);
        } else {
            $logger->save();
            header('location: ./settings.php#pluginBloc');
        }
    }

    public function getGithubMarketRepos() {
        header('Content-Type: application/json');
        echo json_encode($this->getGithubMarketReposInfos($this->getGithubMarketReposList()));
    }

    protected function getGithubMarketReposList() {
        return json_decode(self::getUrlContent("https://api.github.com/orgs/Leed-market/repos?per_page=1000"));
    }

    protected function getGithubMarketReposInfos($repos) {
        $infos = array();
        $installedPluginsNames = $this->getInstalledPluginsNames();
        foreach($repos as $repo) {
            if($repo->archived) {
                continue;
            }
            $repoName = $repo->name;
            if(!in_array(strtolower($repoName), $installedPluginsNames)) {
                $infos[] = array(
                    'name' => $repoName,
                    'description' => isset($repo->description) ? $repo->description : false,
                    'zipUrl' => 'https://github.com/'.$repo->full_name.'/archive/'.$repo->default_branch.'.zip'
                );
            }
        }
        return $infos;
    }

    protected function getInstalledPluginsNames() {
        $names = array();
        $installedPlugins = self::getAll();
        if(!$installedPlugins || empty($installedPlugins)) {
            return $names;
        }
        foreach($installedPlugins as $installedPlugin) {
            $names[] = strtolower($installedPlugin->getName());
        }
        return $names;
    }

    protected static function getUrlContent($url) {
        $timeout = 20;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
        $datas = curl_exec($ch);
        curl_close($ch);
        return $datas;
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

    public static function addLink($rel, $link, $type='', $title='') {
        $GLOBALS['hooks']['head_link'][] = array("rel"=>$rel, "link"=>$link, "type"=>$type, "title"=>$title);
    }

    public static function callLink(){
        $return='';
        if(isset($GLOBALS['hooks']['head_link'])) {
            foreach($GLOBALS['hooks']['head_link'] as $head_link) {
                $return .='<link rel="'.$head_link['rel'].'" href="'.$head_link['link'].'" type="'.$head_link['type'].'" title="'.$head_link['title'].'" />'."\n";
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
        if ($a->getState() == $b->getState())
            if ($a->getName() == $b->getName())
                return 0;
            else
                return $a->getName() < $b->getName() ? -1 : 1;
        else
            return $a->getState() < $b->getState() ? -1 : 1;
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

    function getAddress(){
        return $this->address;
    }

    function setAddress($address){
        $this->address = $address;
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
