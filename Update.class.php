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
     * Description : Récupération des fichiers déjà passé lors des anciennes mises à jour.
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
            die ('Impossible d\'écrire dans le fichier .'.$updateFile.'. Merci d\'ajouter les droits necessaires.');
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

        $alreadyPassed = array();
        $notPassed = array();

        if ($jsonFiles==''){
            $jsonFiles[0] = array();
        }
        foreach($files as $file){
            if(in_array(basename($file), $jsonFiles[0])){
                $alreadyPassed [] =  basename($file);
            }else{
                $notPassed [] =  basename($file);
            }
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

                $conn = new MysqlEntity();
                //on sépare chaque requête par les ;
                $sql_array = explode (";",$sql);
                foreach ($sql_array as $val) {
                    $val = preg_replace('#([-].*)|(\n)#','',$val);
                    if ($val != '') {
                        $conn->customQuery($val);
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