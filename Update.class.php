<?php
/**
 @nom: Update
 @auteur: Maël ILLOUZ (mael.illouz@cobestran.com)
 @description: Classe de gestion des mises à jour en BDD liées aux améliorations apportées dans Leed
 @todo : Ajouter la possiblité d'executer des fichiers php de maj.
 */

class Update{
    const FOLDER = '/updates';
    protected $mysqlEntity;
    protected $currentPatch = array();
    protected $updatesFolderPath = "";

    public function __construct() {
        $this->updatesFolderPath = dirname(__FILE__).self::FOLDER.'/';
    }

    /**
     * Description : Récupération des fichiers déjà passés lors des anciennes mises à jour.
     */
    private static function getUpdateFile(){
        $updateFile = dirname(__FILE__).Update::FOLDER.'/update.json';
        if(!file_exists($updateFile)) {
            if (!touch($updateFile)) {
                die ('Impossible d\'écrire dans le répertoire .'.dirname($updateFile).'. Merci d\'ajouter les droits necessaires.');
            }
        }

        return json_decode(file_get_contents($updateFile),true);
    }

    private static function addUpdateFile($addFile){
        $updateFile = dirname(__FILE__).Update::FOLDER.'/update.json';
        $originFile = Update::getUpdateFile();
        if(empty($originFile))
            $originFile = array();
        $newfile = array_merge($originFile,$addFile);
        if (is_writable($updateFile)){
            file_put_contents($updateFile,json_encode($newfile));
        } else {
            die ('Impossible d\'écrire dans le fichier .'.$updateFile.'. Merci d\'ajouter les droits nécessaires.');
        }
    }


    /**
     * Description : Permet de trouver les fichiers qui n'ont pas encore été joués
     */
    protected function getNewPatch() {
        $sqlFiles = glob($this->updatesFolderPath.'*.sql');
        $phpFiles = glob($this->updatesFolderPath.'*.php');
        $files = array_merge($sqlFiles, $phpFiles);
        if(empty($files))
            $files = array();

        $jsonFiles = Update::getUpdateFile();

        $notPassed = array();

        if ($jsonFiles=='') $jsonFiles[0] = array();

        foreach($files as $file){
            $found = false;
            foreach($jsonFiles as $jsonfile){
                if (isset($jsonfile[0])) {
                    if(in_array(basename($file), $jsonfile)) $found = true;
                }
            }
            if (!$found) $notPassed [] =  basename($file);
        }
        return $notPassed;
    }

    /**
     * Description : Permet l'execution des fichiers sql non joués
     * @simulation : true pour ne pas faire les actions en bdd
     */
    public function executePatch($simulation=false) {
        $newFilesForUpdate = $this->getNewPatch();

        //si aucun nouveau fichier de mise à jour à traiter @return : false
        if(count($newFilesForUpdate)==0) return false;
        if (!$simulation) {
            Functions::purgeRaintplCache();
            $this->setMysqlEntity( new MysqlEntity() );
            foreach($newFilesForUpdate as $newFile){
                $this->setCurrentPatch($newFile);
                if(Functions::endsWith($this->currentPatch['name'], 'php')) {
                    include($this->currentPatch['path']);
                }
                if(Functions::endsWith($this->currentPatch['name'], 'sql')) {
                    $this->makeSqlUpdate();
                }
            }
            $_SESSION = array();
            session_unset();
            session_destroy();
        }
        // quand toutes les requêtes ont été executées, on insert le sql dans le json
        Update::addUpdateFile(array($newFilesForUpdate));

        return true;
    }

    protected function makeSqlUpdate() {
        $sql = file_get_contents($this->currentPatch['path']);
        $sql_array = explode (";",$sql);
        foreach ($sql_array as $val) {
            $val = preg_replace('#([-].*)|(\n)#','',$val);
            if ($val != '') {
                $result = $this->mysqlEntity->customQuery($val);
                if (false===$result) {
                    $this->writeLog('SQL : '.$val);
                    $this->writeLog($this->mysqlEntity->error());
                } else {
                    $this->writeLog('SQL : '.$val);
                    $this->writeLog($this->mysqlEntity->affectedRows().' rows affected');
                }
            }
        }
    }

    protected function writeLog($message) {
        file_put_contents($this->currentPath['logsPath'], date('d/m/Y H:i:s').' : '.$message."\n", FILE_APPEND);
    }

    public function getCurrentPatch() {
        return $this->currentPatch;
    }

    public function setCurrentPatch($fileName) {
        $this->currentPatch = array(
            'name' => $fileName,
            'path' => $this->updatesFolderPath.$fileName,

            'logsPath' => $this->updatesFolderPath.substr($fileName,0,strlen($fileName)-3).'log'
        );
    }

    public function getMysqlEntity() {
        return $this->mysqlEntity;
    }

    public function setMysqlEntity(MysqlEntity $mysqlEntity) {
        $this->mysqlEntity = $mysqlEntity;
    }

}

?>
