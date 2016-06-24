<?php
/**
 @nom: Update
 @auteur: Maël ILLOUZ (mael.illouz@cobestran.com)
 @description: Classe de gestion des mises à jour en BDD liées aux améliorations apportées dans Leed
 @todo : Ajouter la possiblité d'executer des fichiers php de maj.
 */

class Update{
    const FOLDER = '/updates';

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
    private static function getNewPatch() {
        $files = glob(dirname(__FILE__). Update::FOLDER .'/*.sql');
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
    public static function ExecutePatch($simulation=false) {
        $newFilesForUpdate = Update::getNewPatch();

        //si aucun nouveau fichier de mise à jour à traiter @return : false
        if(count($newFilesForUpdate)==0) return false;
        if (!$simulation) {
            foreach($newFilesForUpdate as $file){
                // récupération du contenu du sql
                $sql = file_get_contents(dirname(__FILE__).Update::FOLDER.'/'.$file);

                $conn = MysqlConnector::getInstance()->connection;
                //on sépare chaque requête par les ;
                $sql_array = explode (";",$sql);
                foreach ($sql_array as $val) {
                    $val = preg_replace('#([-].*)|(\n)#','',$val);
                    if ($val != '') {
                        //remplacement des préfixes de table
                        $val = str_replace('##MYSQL_PREFIX##',MYSQL_PREFIX,$val);
                        $result = $conn->query($val);
                        $ficlog = dirname(__FILE__).Update::FOLDER.'/'.substr($file,0,strlen($file)-3).'log';
                        if (false===$result) {
                            file_put_contents($ficlog, date('d/m/Y H:i:s').' : SQL : '.$val."\n", FILE_APPEND);
                            file_put_contents($ficlog, date('d/m/Y H:i:s').' : '.$conn->error."\n", FILE_APPEND);
                        } else {
                            file_put_contents($ficlog, date('d/m/Y H:i:s').' : SQL : '.$val."\n", FILE_APPEND);
                            file_put_contents($ficlog, date('d/m/Y H:i:s').' : '.$conn->affected_rows.' rows affected'."\n", FILE_APPEND);
                        }
                    }
                }
                unset($conn);
            }
            $_SESSION = array();
            session_unset();
            session_destroy();
        }
        // quand toutes les requêtes ont été executées, on insert le sql dans le json
        Update::addUpdateFile(array($newFilesForUpdate));

        return true;
    }

}

?>