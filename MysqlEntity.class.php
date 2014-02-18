<?php
require_once('constant.php');
require_once('MysqlConnector.class.php');
/*
    @nom: MysqlEntity
    @auteur: Valentin CARRUESCO (valentincarruesco@yahoo.fr)
    @date de création: 16/04/2012 02:34:15
    @description: Classe parent de tous les modèles (classe entitées) liées a la base de donnée,
     cette classe est configuré pour agir avec une base MySQL, mais il est possible de redefinir ses codes SQL pour l'adapter à un autre SGBD sans affecter
     le reste du code du projet.

*/

class MysqlEntity
{

    private $debug = false;
    private $debugAllQuery = false;


    function sgbdType($type){
        $return = false;
        switch($type){
            case 'string':
            case 'timestamp':
                $return = 'VARCHAR(225) CHARACTER SET utf8 COLLATE utf8_general_ci';
            break;
            case 'longstring':
                $return = 'TEXT CHARACTER SET utf8 COLLATE utf8_general_ci';
            break;
            case 'key':
                $return = 'int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY';
            break;
            case 'object':
            case 'integer':
                $return = 'INT(11)';
            break;
            case 'boolean':
                $return = 'INT(1)';
            break;
            default;
                $return = 'TEXT CHARACTER SET utf8 COLLATE utf8_general_ci';
            break;
        }
        return $return ;
    }

    /**
     * Protège une variable pour MySQL
     */
    protected function secure($value, $field){
        $type = false;

        // ce champ n'existe pas : on le considère comme une chaîne de caractères
        if (isset($this->object_fields[$field]))
            $type = $this->object_fields[$field];

        $return = false;
        switch($type){
            case 'key':
            case 'object':
            case 'integer':
            case 'boolean':
                $return = intval($value);
            break;
            case 'string':
            case 'timestamp':
            case 'longstring':
            default;
                $return = mysql_real_escape_string((string)$value);
            break;
        }
        return $return ;
    }

    public function __construct(){
        MysqlConnector::getInstance();
    }

    public function __destruct(){

    }

    // GESTION SQL

    /**
    * Methode de suppression de l'entité
    * @author Valentin CARRUESCO
    * @category manipulation SQL
    * @param <String> $debug=false active le debug mode (0 ou 1)
    * @return Aucun retour
    */
    public function destroy($debug=false)
    {
        $query = 'DROP TABLE IF EXISTS '.MYSQL_PREFIX.$this->TABLE_NAME.';';
        if($this->debug)echo '<hr>'.$this->CLASS_NAME.' ('.__METHOD__ .') : Requete --> '.$query.'<br>'.mysql_error();
        $myQuery = $this->customQuery($query);
    }

    /**
    * Methode de nettoyage de l'entité
    * @author Valentin CARRUESCO
    * @category manipulation SQL
    * @param <String> $debug=false active le debug mode (0 ou 1)
    * @return Aucun retour
    */
    public function truncate($debug=false)
    {
        $query = 'TRUNCATE TABLE '.MYSQL_PREFIX.$this->TABLE_NAME.';';
        if($this->debug)echo '<hr>'.$this->CLASS_NAME.' ('.__METHOD__ .') : Requete --> '.$query.'<br>'.mysql_error();
        $myQuery = $this->customQuery($query);
    }

    /**
    * Methode de creation de l'entité
    * @author Valentin CARRUESCO
    * @category manipulation SQL
    * @param <String> $debug=false active le debug mode (0 ou 1)
    * @return Aucun retour
    */
    public function create($debug=false){
        $query = 'CREATE TABLE IF NOT EXISTS `'.MYSQL_PREFIX.$this->TABLE_NAME.'` (';

        $i=false;
        foreach($this->object_fields as $field=>$type){
            if($i){$query .=',';}else{$i=true;}
            $query .='`'.$field.'`  '. $this->sgbdType($type).'  NOT NULL';
        }
        if (isset($this->object_fields_index)){
            foreach($this->object_fields_index as $field=>$type){
                $query .= ',KEY `index'.$field.'` (`'.$field.'`)';
            }
        }
        $query .= ')
        ENGINE InnoDB,
        DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci
        ;';
        if($this->debug)echo '<hr>'.$this->CLASS_NAME.' ('.__METHOD__ .') : Requete --> '.$query.'<br>'.mysql_error();
        $myQuery = $this->customQuery($query);
    }

    public function massiveInsert($events){
        if (empty($events)) return;
        $query = 'INSERT INTO `'.MYSQL_PREFIX.$this->TABLE_NAME.'`(';
            $i=false;
            foreach($this->object_fields as $field=>$type){
                if($type!='key'){
                    if($i){$query .=',';}else{$i=true;}
                    $query .='`'.$field.'`';
                }
            }
            $query .=') select';
            $u = false;

            foreach($events as $event){

                if($u){$query .=' union select ';}else{$u=true;}

                $i=false;
                foreach($event->object_fields as $field=>$type){
                    if($type!='key'){
                        if($i){$query .=',';}else{$i=true;}
                        $query .='"'.$this->secure($event->$field, $field).'"';
                    }
                }


            }

            $query .=';';
            if($this->debug)echo '<hr>'.$this->CLASS_NAME.' ('.__METHOD__ .') : Requete --> '.$query.'<br>'.mysql_error();

        $this->customQuery($query);
    }

    /**
    * Methode d'insertion ou de modifications d'elements de l'entité
    * @author Valentin CARRUESCO
    * @category manipulation SQL
    * @param  Aucun
    * @return Aucun retour
    */
    public function save(){
        if(isset($this->id)){
            $query = 'UPDATE `'.MYSQL_PREFIX.$this->TABLE_NAME.'`';
            $query .= ' SET ';

            $i=false;
            foreach($this->object_fields as $field=>$type){
                if($i){$query .=',';}else{$i=true;}
                $id = $this->$field;
                $query .= '`'.$field.'`="'.$this->secure($id, $field).'"';
            }

            $query .= ' WHERE `id`="'.$this->id.'";';
        }else{
            $query = 'INSERT INTO `'.MYSQL_PREFIX.$this->TABLE_NAME.'`(';
            $i=false;
            foreach($this->object_fields as $field=>$type){
                if($i){$query .=',';}else{$i=true;}
                $query .='`'.$field.'`';
            }
            $query .=')VALUES(';
            $i=false;
            foreach($this->object_fields as $field=>$type){
                if($i){$query .=',';}else{$i=true;}
                $query .='"'.$this->secure($this->$field, $field).'"';
            }

            $query .=');';
        }
        if($this->debug)echo '<hr>'.$this->CLASS_NAME.' ('.__METHOD__ .') : Requete --> '.$query.'<br>'.mysql_error();
        $this->customQuery($query);
        $this->id =  (!isset($this->id)?mysql_insert_id():$this->id);
    }

    /**
    * Méthode de modification d'éléments de l'entité
    * @author Valentin CARRUESCO
    * @category manipulation SQL
    * @param <Array> $colonnes=>$valeurs
    * @param <Array> $colonnes (WHERE) =>$valeurs (WHERE)
    * @param <String> $operation="=" definis le type d'operateur pour la requete select
    * @param <String> $debug=false active le debug mode (0 ou 1)
    * @return Aucun retour
    */
    public function change($columns,$columns2,$operation='=',$debug=false){
        $query = 'UPDATE `'.MYSQL_PREFIX.$this->TABLE_NAME.'` SET ';
        $i=false;
        foreach ($columns as $column=>$value){
            if($i){$query .=',';}else{$i=true;}
            $query .= '`'.$column.'`="'.$this->secure($value, $column).'" ';
        }
        $query .=' WHERE ';

        $i = false;
        foreach ($columns2 as $column=>$value){
            if($i){$query .='AND ';}else{$i=true;}
            $query .= '`'.$column.'`'.$operation.'"'.$this->secure($value, $column).'" ';
        }
        if($this->debug)echo '<hr>'.$this->CLASS_NAME.' ('.__METHOD__ .') : Requete --> '.$query.'<br>'.mysql_error();
        $this->customQuery($query);
    }

    /**
    * Méthode de selection de tous les elements de l'entité
    * @author Valentin CARRUESCO
    * @category manipulation SQL
    * @param <String> $ordre=null
    * @param <String> $limite=null
    * @param <String> $debug=false active le debug mode (0 ou 1)
    * @return <Array<Entity>> $Entity
    */
    public function populate($order=null,$limit=null,$debug=false){
        $results = $this->loadAll(array(),$order,$limit,'=',$debug);
        return $results;
    }

    /**
    * Méthode de selection multiple d'elements de l'entité
    * @author Valentin CARRUESCO
    * @category manipulation SQL
    * @param <Array> $colonnes (WHERE)
    * @param <Array> $valeurs (WHERE)
    * @param <String> $ordre=null
    * @param <String> $limite=null
    * @param <String> $operation="=" definis le type d'operateur pour la requete select
    * @param <String> $debug=false active le debug mode (0 ou 1)
    * @return <Array<Entity>> $Entity
    */
    public function loadAll($columns,$order=null,$limit=null,$operation="=",$debug=false,$selColumn='*'){
        $objects = array();
        $whereClause = '';

            if($columns!=null && sizeof($columns)!=0){
            $whereClause .= ' WHERE ';
                $i = false;
                foreach($columns as $column=>$value){
                    if($i){$whereClause .=' AND ';}else{$i=true;}
                    $whereClause .= '`'.$column.'`'.$operation.'"'.$this->secure($value, $column).'"';
                }
            }
            $query = 'SELECT '.$selColumn.' FROM `'.MYSQL_PREFIX.$this->TABLE_NAME.'` '.$whereClause.' ';
            if($order!=null) $query .='ORDER BY '.$order.' ';
            if($limit!=null) $query .='LIMIT '.$limit.' ';
            $query .=';';

            if($this->debug)echo '<hr>'.$this->CLASS_NAME.' ('.__METHOD__ .') : Requete --> '.$query.'<br>'.mysql_error();
            $execQuery = $this->customQuery($query);
            while($queryReturn = mysql_fetch_assoc($execQuery)){

                $object = new $this->CLASS_NAME();
                foreach($this->object_fields as $field=>$type){
                    if(isset($queryReturn[$field])) $object->$field = $queryReturn[$field];
                }
                $objects[] = $object;
                unset($object);
            }
            return $objects;
    }

    public function loadAllOnlyColumn($selColumn,$columns,$order=null,$limit=null,$operation="=",$debug=false){
        $objects = $this->loadAll($columns,$order,$limit,$operation,$debug,$selColumn);
        if(count($objects)==0)$objects = array();
        return $objects;
    }


    /**
    * Méthode de selection unique d'élements de l'entité
    * @author Valentin CARRUESCO
    * @category manipulation SQL
    * @param <Array> $colonnes (WHERE)
    * @param <Array> $valeurs (WHERE)
    * @param <String> $operation="=" definis le type d'operateur pour la requete select
    * @param <String> $debug=false active le debug mode (0 ou 1)
    * @return <Entity> $Entity ou false si aucun objet n'est trouvé en base
    */
    public function load($columns,$operation='=',$debug=false){
        $objects = $this->loadAll($columns,null,1,$operation,$debug);
        if(!isset($objects[0]))$objects[0] = false;
        return $objects[0];
    }

    /**
    * Méthode de selection unique d'élements de l'entité
    * @author Valentin CARRUESCO
    * @category manipulation SQL
    * @param <Array> $colonnes (WHERE)
    * @param <Array> $valeurs (WHERE)
    * @param <String> $operation="=" definis le type d'operateur pour la requete select
    * @param <String> $debug=false active le debug mode (0 ou 1)
    * @return <Entity> $Entity ou false si aucun objet n'est trouvé en base
    */
    public function getById($id,$operation='=',$debug=false){
        return $this->load(array('id'=>$id),$operation,$debug);
    }

    /**
    * Methode de comptage des éléments de l'entité
    * @author Valentin CARRUESCO
    * @category manipulation SQL
    * @param <String> $debug=false active le debug mode (0 ou 1)
    * @return<Integer> nombre de ligne dans l'entité'
    */
    public function rowCount($columns=null)
    {
        $whereClause ='';
        if($columns!=null){
            $whereClause = ' WHERE ';
            $i=false;
            foreach($columns as $column=>$value){
                if($i){$whereClause .=' AND ';}else{$i=true;}
                $whereClause .= '`'.$column.'`="'.$this->secure($value, $column).'"';
            }
        }
        $query = 'SELECT COUNT(1) FROM '.MYSQL_PREFIX.$this->TABLE_NAME.$whereClause;
        if($this->debug)echo '<hr>'.$this->CLASS_NAME.' ('.__METHOD__ .') : Requete --> '.$query.'<br>'.mysql_error();
        $myQuery = $this->customQuery($query);
        $number = mysql_fetch_array($myQuery);
        return $number[0];
    }

    /**
    * Méthode de suppression d'éléments de l'entité
    * @author Valentin CARRUESCO
    * @category manipulation SQL
    * @param <Array> $colonnes (WHERE)
    * @param <Array> $valeurs (WHERE)
    * @param <String> $operation="=" definis le type d'operateur pour la requete select
    * @param <String> $debug=false active le debug mode (0 ou 1)
    * @return Aucun retour
    */
    public function delete($columns,$operation='=',$debug=false){
        $whereClause = '';

        $i=false;
        foreach($columns as $column=>$value){
            if($i){$whereClause .=' AND ';}else{$i=true;}
            $whereClause .= '`'.$column.'`'.$operation.'"'.$this->secure($value, $column).'"';
        }
        $query = 'DELETE FROM `'.MYSQL_PREFIX.$this->TABLE_NAME.'` WHERE '.$whereClause.' ;';
        if($this->debug)echo '<hr>'.$this->CLASS_NAME.' ('.__METHOD__ .') : Requete --> '.$query.'<br>'.mysql_error();
        $this->customQuery($query);

    }

    ///@TODO: pourquoi deux méthodes différentes qui font la même chose ?
    public function customExecute($request){
        if($this->debugAllQuery)echo '<hr>'.$this->CLASS_NAME.' ('.__METHOD__ .') : Requete --> '.$request.'<br>'.mysql_error();
        $result = mysql_query($request);
        if (false===$result) {
            throw new Exception(mysql_error());
        }
        return $result;
    }
    public function customQuery($request){
        if($this->debugAllQuery)echo '<hr>'.$this->CLASS_NAME.' ('.__METHOD__ .') : Requete --> '.$request.'<br>'.mysql_error();
        $result = mysql_query($request);
        if (false===$result) {
            throw new Exception(mysql_error());
        }
        return $result;
    }


    // ACCESSEURS
        /**
    * Méthode de récuperation de l'attribut debug de l'entité
    * @author Valentin CARRUESCO
    * @category Accesseur
    * @param Aucun
    * @return <Attribute> debug
    */

    public function getDebug(){
        return $this->debug;
    }

    /**
    * Méthode de définition de l'attribut debug de l'entité
    * @author Valentin CARRUESCO
    * @category Accesseur
    * @param <boolean> $debug
    */

    public function setDebug($debug){
        $this->debug = $debug;
    }

    public function getObject_fields(){
        return $this->object_fields;
    }

    /**
    * @return <boolean> VRAI si la table existe, FAUX sinon
    */

    public function tableExists() {
        $table = MYSQL_PREFIX.$this->TABLE_NAME;
        $result = $this->customQuery("SHOW TABLES LIKE '$table'");
        $assoc = mysql_fetch_assoc($result);
        return false===$assoc ? false : true;
    }
}
?>
