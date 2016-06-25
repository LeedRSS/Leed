<?php

/*
 @nom: constant
 @auteur: Idleman (idleman@idleman.fr)
 @description:  Classe de stockage des fonctions utiles (toutes disponibles en static)
 */

class Functions
{
    private $id;
    public $debug=0;

    /**
     * Securise la variable utilisateur entrée en parametre
     * @author Valentin
     * @param<String> variable a sécuriser
     * @param<Integer> niveau de securisation
     * @return<String> variable securisée
     */

    public static function secure($var,$level = 1){
        $var = htmlspecialchars($var, ENT_QUOTES, "UTF-8");
        if($level<1)$var = mysqli_real_escape_string($var);
        if($level<2)$var = addslashes($var);
        return $var;
    }


    /**
     * Return l'environnement/serveur sur lequel on se situe, permet de changer les
     * connexions bdd en fonction de la dev, la préprod ou la prod
     */
    public static function whereImI(){

        $maps = array (
        'LOCAL'=>array('localhost','127.0.0.1','0.0.0.1','::0.0.0.0'),
        'LAN'=>array('192.168.10.','valentin'),
        'PWAN'=>array('test.sys1.fr'),
        'WAN'=>array('www.sys1.fr'),
        );


        $return = 'UNKNOWN';
        foreach($maps as $map=>$values){

            foreach($values as $ip){
                $pos = strpos(strtolower($_SERVER['HTTP_HOST']),$ip);
                if ($pos!==false){
                    $return = $map;
                }
            }
        }
        return $return;
    }

    public static function isLocal($perimeter='LOCAL'){
        $return = false;

        $localTab = array('localhost','127.0.0.1','0.0.0.1','::0.0.0.0');
        $lanTab = array('192.168.10.','valentin');

        switch($perimeter){
            case 'LOCAL':
                foreach($localTab as $ip){
                    $pos = strpos(strtolower($_SERVER['HTTP_HOST']),$ip);
                    if ($pos!==false){
                        $return = true;
                    }
                }
                break;
            case 'LAN':
                foreach($lanTab as $ip){
                    $pos = strpos(strtolower($_SERVER['HTTP_HOST']),$ip);
                    if ($pos!==false){
                        $return = true;
                    }
                }
                break;
            case 'ALL':
                foreach($localTab as $ip){
                    $pos = strpos(strtolower($_SERVER['HTTP_HOST']),$ip);
                    if ($pos!==false){
                        $return = true;
                    }
                }
                foreach($lanTab as $ip){
                    $pos = strpos(strtolower($_SERVER['HTTP_HOST']),$ip);
                    if ($pos!==false){
                        $return = true;
                    }
                }
                break;
        }

        return $return;
    }


    /**
     * Convertis la chaine passée en timestamp quel que soit sont format
     * (prend en charge les formats type dd-mm-yyy , dd/mm/yyy, yyyy/mm/ddd...)
     */
    public static function toTime($string){
        $string = str_replace('/','-',$string);
        $string = str_replace('\\','-',$string);

        $string = str_replace('Janvier','Jan',$string);
        $string = str_replace('Fevrier','Feb',$string);
        $string = str_replace('Mars','Mar',$string);
        $string = str_replace('Avril','Apr',$string);
        $string = str_replace('Mai','May',$string);
        $string = str_replace('Juin','Jun',$string);
        $string = str_replace('Juillet','Jul',$string);
        $string = str_replace('Aout','Aug',$string);
        $string = str_replace('Septembre','Sept',$string);
        $string = str_replace('Octobre','Oct',$string);
        $string = str_replace('Novembre','Nov',$string);
        $string = str_replace('Decembre','Dec',$string);
        return strtotime($string);
    }

    /**
     * Recupere l'ip de l'internaute courant
     * @author Valentin
     * @return<String> ip de l'utilisateur
     */

    public static function getIP(){
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];}
            elseif(isset($_SERVER['HTTP_CLIENT_IP'])){
                $ip = $_SERVER['HTTP_CLIENT_IP'];}
                else{ $ip = $_SERVER['REMOTE_ADDR'];}
                return $ip;
    }

    /**
     * Retourne une version tronquée au bout de $limit caracteres de la chaine fournie
     * @author Valentin
     * @param<String> message a tronquer
     * @param<Integer> limite de caracteres
     * @return<String> chaine tronquée
     */
    public static function truncate($msg,$limit){
        if(mb_strlen($msg)>$limit){
            $fin='…' ;
            $nb=$limit-mb_strlen($fin) ;
        }else{
            $nb=mb_strlen($msg);
            $fin='';
        }
        return mb_substr($msg, 0, $nb).$fin;
    }


    function getExtension($fileName){
        $dot = explode('.',$fileName);
        return $dot[sizeof($dot)-1];
    }

    /**
     * Definis si la chaine fournie est existante dans la reference fournie ou non
     * @param unknown_type $string
     * @param unknown_type $reference
     * @return false si aucune occurence du string, true dans le cas contraire
     */
    public static function contain($string,$reference){
        $return = true;
        $pos = strpos($reference,$string);
        if ($pos === false) {
            $return = false;
        }
        return strtolower($return);
    }

    /**
     * Définis si la chaine passée en parametre est une url ou non
     */
    public static function isUrl($url){
        $return =false;
        if (preg_match('/^(http|https|ftp)://([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?/?/i', $url)) {
            $return =true;
        }
        return $return;
    }

    /**
     * Définis si la chaine passée en parametre est une couleur héxadécimale ou non
     */
    public static function isColor($color){
        $return =false;
        if (preg_match('/^#(?:(?:[a-fd]{3}){1,2})$/i', $color)) {
            $return =true;
        }
        return $return;
    }

    /**
     * Définis si la chaine passée en parametre est un mail ou non
     */
    public static function isMail($mail){
        $return =false;
        if (filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            $return =true;
        }
        return $return;
    }

    /**
     * Définis si la chaine passée en parametre est une IP ou non
     */
    public static function isIp($ip){
        $return =false;
        if (preg_match('^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$',$ip)) {
            $return =true;
        }
        return $return;
    }

    public static function sourceName($string){
        $name = strtolower($string);
        $name = str_replace(' ','-',$name);
        $name = str_replace('&#039;','-',$name);
        $name = str_replace('\'','-',$name);
        $name = str_replace(',','-',$name);
        $name = str_replace(':','-',$name);
        $name = str_replace('&agrave;','a',$name);
        $name = trim($name);
        $name = html_entity_decode($name,null,'UTF-8');
        return $name;
    }

    public static function makeCookie($name, $value, $expire='') {
        if($expire == '') {
            setcookie($name, $value, mktime(0,0,0, date("d"),
            date("m"), (date("Y")+1)),'/');
        }else {
            setcookie($name, '', mktime(0,0,0, date("d"),
            date("m"), (date("Y")-1)),'/');
        }
    }

    public static function destroyCookie($name){
        Fonction::makeCookie($name,'',time()-3600);
        unset($_COOKIE[$name]);
    }

    public static function wordwrap($str, $width = 75, $break = "\n", $cut = false)
    {
        $str = html_entity_decode($str);
        $str =  htmlentities (wordwrap($str,$width,$break,$cut));
        $str = str_replace('&lt;br/&gt;','<br/>',$str);
        $str = str_replace('&amp;','&',$str);
        return $str;
    }

    public static function createFile($filePath,$content){
        $fichier = fopen($filePath,"w+");
        $fwriteResult = fwrite($fichier,$content);
        fclose($fichier);
    }



    public static function convertFileSize($bytes)
    {
         if($bytes<1024){
             return round(($bytes / 1024), 2).' o';
         }elseif(1024<$bytes && $bytes<1048576){
             return round(($bytes / 1024), 2).' ko';
         }elseif(1048576<$bytes && $bytes<1073741824){
             return round(($bytes / 1024)/1024, 2).' Mo';
         }elseif(1073741824<$bytes){
             return round(($bytes / 1024)/1024/1024, 2).' Go';
         }
    }


    public static function hexaValue($str){
        $code = dechex(crc32($str));
          $code = substr($code, 0, 6);
          return $code;
    }

    public static function scanRecursiveDir($dir){
        $files = scandir($dir);
        $allFiles = array();
        foreach($files as $file){
            if($file!='.' && $file!='..'){
                if(is_dir($dir.$file)){
                    $allFiles = array_merge($allFiles,Fonction::scanRecursiveDir($dir.$file));
                }else{
                    $allFiles[]=str_replace('//','/',$dir.'/'.$file);
                }
            }
        }
        return $allFiles;
    }

    /** Permet la sortie directe de texte à l'écran, sans tampon.
        Source : http://php.net/manual/fr/function.flush.php
    */
    public static function triggerDirectOutput() {
        // La ligne de commande n'en a pas besoin.
        if ('cli'==php_sapi_name()) return;
        if (function_exists('apache_setenv')) {
            /* Selon l'hébergeur la fonction peut être désactivée. Alors Php
               arrête le programme avec l'erreur :
               "PHP Fatal error:  Call to undefined function apache_setenv()".
            */
            @apache_setenv('no-gzip', 1);
        }
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);
        for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }
        ob_implicit_flush(1);
    }

    public static function relativePath($from, $to, $ps = '/') {
        $arFrom = explode($ps, rtrim($from, $ps));
        $arTo = explode($ps, rtrim($to, $ps));
        while(count($arFrom) && count($arTo) && ($arFrom[0] == $arTo[0])) {
            array_shift($arFrom);
            array_shift($arTo);
        }
        return str_pad("", count($arFrom) * 3, '..'.$ps).implode($ps, $arTo);
    }


    // Nettoyage de l'url avant la mise en base
    public static function clean_url( $url ) {
        $url = str_replace('&amp;', '&', $url);
        return $url;
    }



    /**
    * Méthode de test de connexion.
    * @return true si ok
    * @param server
    * @param login
    * @param pass
    * @param db facultatif, si précisé alors tente de la séléctionner
    */
    public static function testDb($server, $login, $pass, $db=null) {
        /* Méthode hors des classes dédiées aux BDD afin de supporter le moins
           de dépendances possibles. En particulier, pas besoin que le fichier
           de configuration existe. */
        $link = mysqli_connect($server, $login, $pass, $db);
        if (false===$link) return false;
        mysqli_close($link);
        return true;
    }

    /**
    * @return les langues acceptées par le navigateur
    */
    public static function getBrowserLanguages() {
        /* http://www.w3.org/International/questions/qa-lang-priorities.en.php
         * ex: da, en-gb;q=0.8,en;q=0.7 --> array('da','en');
        */
        if (!array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) return array();
        $languages = array();
        $chunks = preg_split('/,\s*/', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        foreach($chunks as $chunk) $languages []= substr($chunk, 0, 2);
        return array_unique($languages);
    }

    /**
    * @return boolean
    */
    public static function isAjaxCall() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}
?>
