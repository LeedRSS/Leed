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

    protected $dbconnector = false;
    protected $debug = false;
    protected $debugAllQuery = false;
    protected $preparedValues = array();


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
            case 'extralongstring':
                $return = 'MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci';
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

    protected function prepareValue($value, $field){
        $defaultValue = false;
        $type = isset($this->object_fields[$field]) ?
            $this->object_fields[$field]
            : false;
        switch($type){
            case 'key':
            case 'object':
            case 'integer':
            case 'boolean':
                $defaultValue = 0;
            break;
            case 'string':
            case 'timestamp':
            case 'longstring':
            default;
                $defaultValue = '';
            break;
        }
        if($field !== 'id' && is_null($value)) {
            $value = $defaultValue;
        }
        array_push($this->preparedValues, $value);
    }

    protected function connect() {
        $this->dbconnector = MysqlConnector::getInstance();
    }

    public function __construct(){
        $this->connect();
    }

    function __sleep() {
        $variableList = array('debug', 'debugAllQuery');
        if (isset($this->object_fields)) {
            // Une sous-classe définit sa liste de champs de BDD
            $variableList = array_merge($variableList, array_keys($this->object_fields));
        }
        return $variableList;
    }

    function __wakeup() {
        $this->connect();
    }


    // GESTION SQL

    /**
    * Methode de suppression de l'entité
    * @author Valentin CARRUESCO
    * @category manipulation SQL
    * @return Aucun retour
    */
    public function destroy()
    {
        $query = 'DROP TABLE IF EXISTS `'.MYSQL_PREFIX.$this->TABLE_NAME.'`;';
        if($this->debug) echo '<hr>'.get_class($this).' ('.__METHOD__ .') : Requete --> '.$query.'<br>'.$this->dbconnector->connection->errorInfo()[2];
        $myQuery = $this->customQuery($query);
    }

    /**
    * Methode de nettoyage de l'entité
    * @author Valentin CARRUESCO
    * @category manipulation SQL
    * @return Aucun retour
    */
    public function truncate()
    {
        $query = 'TRUNCATE TABLE `'.MYSQL_PREFIX.$this->TABLE_NAME.'`;';
        if($this->debug) echo '<hr>'.get_class($this).' ('.__METHOD__ .') : Requete --> '.$query.'<br>'.$this->dbconnector->connection->errorInfo()[2];
        $myQuery = $this->customQuery($query);
    }

    /**
    * Methode de creation de l'entité
    * @author Valentin CARRUESCO
    * @category manipulation SQL
    * @return Aucun retour
    */
    public function create(){
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
        if (isset($this->object_fields_uniques)){
            foreach($this->object_fields_uniques as $field){
                $query .= ',UNIQUE `unique'.$field.'` (`'.$field.'`)';
            }
        }
        $query .= ')
        ENGINE InnoDB,
        DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci
        ;';
        if($this->debug) echo '<hr>'.get_class($this).' ('.__METHOD__ .') : Requete --> '.$query.'<br>'.$this->dbconnector->connection->errorInfo()[2];
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
                        $this->prepareValue($event->$field, $field);
                        $query .='?';
                    }
                }


            }

            $query .=';';
            if($this->debug) echo '<hr>'.get_class($this).' ('.__METHOD__ .') : Requete --> '.$query.'<br>'.$this->dbconnector->connection->errorInfo()[2];

        $this->customQuery($query);
    }

    /**
    * Methode d'insertion ou de modifications d'elements de l'entité
    * @author Valentin CARRUESCO
    * @category manipulation SQL
    * @param  Aucun
    * @return Aucun retour
    */
    public function save($id_field='id'){
        if(isset($this->$id_field)){
            $query = 'UPDATE `'.MYSQL_PREFIX.$this->TABLE_NAME.'`';
            $query .= ' SET ';

            $i=false;
            foreach($this->object_fields as $field=>$type){
                if($i){$query .=',';}else{$i=true;}
                $id = $this->$field;
                $this->prepareValue($id, $field);
                $query .= '`'.$field.'`=?';
            }

            $query .= ' WHERE `'.$id_field.'`="'.$this->$id_field.'";';
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
                $this->prepareValue($this->$field, $field);
                $query .='?';
            }

            $query .=');';
        }
        if($this->debug) echo '<hr>'.get_class($this).' ('.__METHOD__ .') : Requete --> '.$query.'<br>'.$this->dbconnector->connection->errorInfo()[2];
        $this->customQuery($query);
        $this->$id_field = isset($this->$id_field)?
            $this->$id_field
            : $this->dbconnector->connection->lastInsertId();
    }

    /**
    * Méthode de modification d'éléments de l'entité
    * @author Valentin CARRUESCO
    * @category manipulation SQL
    * @param <Array> $colonnes=>$valeurs
    * @param <Array> $colonnes (WHERE) =>$valeurs (WHERE)
    * @param <String> $operation="=" definis le type d'operateur pour la requete select
    * @return Aucun retour
    */
    public function change($columns,$columns2,$operation='='){
        $query = 'UPDATE `'.MYSQL_PREFIX.$this->TABLE_NAME.'` SET ';
        $i=false;
        foreach ($columns as $column=>$value){
            if($i){$query .=',';}else{$i=true;}
            $this->prepareValue($value, $column);
            $query .= '`'.$column.'`=? ';
        }
        $query .= $this->getWhereClause($columns2, $operation);

        if($this->debug) echo '<hr>'.get_class($this).' ('.__METHOD__ .') : Requete --> '.$query.'<br>'.$this->dbconnector->connection->errorInfo()[2];
        $this->customQuery($query);
    }

    /**
    * Méthode de selection de tous les elements de l'entité
    * @author Valentin CARRUESCO
    * @category manipulation SQL
    * @param <String> $ordre=null
    * @param <String> $limite=null
    * @return <Array<Entity>> $Entity
    */
    public function populate($order=null,$limit=null){
        $results = $this->loadAll(array(),$order,$limit,'=');
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
    * @return <Array<Entity>> $Entity
    */
    public function loadAll($columns,$order=null,$limit=null,$operation="=",$selColumn='*'){
        $objects = array();
        $whereClause = $this->getWhereClause($columns,$operation);

            $query = 'SELECT '.$selColumn.' FROM `'.MYSQL_PREFIX.$this->TABLE_NAME.'` '.$whereClause.' ';
            if($order!=null) $query .='ORDER BY '.$order.' ';
            if($limit!=null) $query .='LIMIT '.$limit.' ';
            $query .=';';

            if($this->debug) echo '<hr>'.get_class($this).' ('.__METHOD__ .') : Requete --> '.$query.'<br>'.$this->dbconnector->connection->errorInfo()[2];
            $result = $this->customQuery($query);
            while($queryReturn = $result->fetch(PDO::FETCH_ASSOC)){

                $thisClass = get_class($this);
                $object = new $thisClass();
                foreach($this->object_fields as $field=>$type){
                    if(isset($queryReturn[$field])) $object->$field = $queryReturn[$field];
                }
                $objects[] = $object;
                unset($object);
            }
            return $objects;
    }

    public function loadAllOnlyColumn($selColumn,$columns,$order=null,$limit=null,$operation="="){
        $objects = $this->loadAll($columns,$order,$limit,$operation,$selColumn);
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
    * @return <Entity> $Entity ou false si aucun objet n'est trouvé en base
    */
    public function load($columns,$operation='='){
        $objects = $this->loadAll($columns,null,1,$operation);
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
    * @return <Entity> $Entity ou false si aucun objet n'est trouvé en base
    */
    public function getById($id,$operation='='){
        return $this->load(array('id'=>$id),$operation);
    }

    /**
    * Methode de comptage des éléments de l'entité
    * @author Valentin CARRUESCO
    * @category manipulation SQL
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
                $this->prepareValue($value, $column);
                $whereClause .= '`'.$column.'`=?';
            }
        }
        $query = 'SELECT COUNT(1) FROM `'.MYSQL_PREFIX.$this->TABLE_NAME.'`'.$whereClause;
        if($this->debug) echo '<hr>'.get_class($this).' ('.__METHOD__ .') : Requete --> '.$query.'<br>'.$this->dbconnector->connection->errorInfo()[2];
        $myQuery = $this->customQuery($query);
        $number = $myQuery->fetch(PDO::FETCH_NUM);
        return $number[0];
    }

    /**
    * Méthode de suppression d'éléments de l'entité
    * @author Valentin CARRUESCO
    * @category manipulation SQL
    * @param <Array> $colonnes (WHERE)
    * @param <Array> $valeurs (WHERE)
    * @param <String> $operation="=" definis le type d'operateur pour la requete select
    * @return Aucun retour
    */
    public function delete($columns,$operation='='){
        $whereClause = '';

        $i=false;
        foreach($columns as $column=>$value){
            if($i){$whereClause .=' AND ';}else{$i=true;}
            $this->prepareValue($value, $column);
            $whereClause .= '`'.$column.'`'.$operation.'?';
        }
        $query = 'DELETE FROM `'.MYSQL_PREFIX.$this->TABLE_NAME.'` WHERE '.$whereClause.' ;';
        if($this->debug) echo '<hr>'.get_class($this).' ('.__METHOD__ .') : Requete --> '.$query.'<br>'.$this->dbconnector->connection->errorInfo()[2];
        $this->customQuery($query);

    }

    public function customQuery($request){
        try {
            if($this->debugAllQuery) echo '<hr>'.get_class($this).' ('.__METHOD__ .') : Requete --> '.$request.'<br>'.$this->dbconnector->connection->errorInfo()[2];
            $stmt = $this->dbconnector->connection->prepare($request);
            $result = $stmt->execute($this->preparedValues);
            $this->preparedValues = array();
            return $stmt;
        }catch(PDOException  $e) {
            echo "Error: ".$e;
            $error = $this->error();
            if ($error) {
                error_log('Leed error: '.$this->error());
                error_log('Leed query: '.$request);
            }
        }
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
        $count = $result->rowCount();
        return $count > 0;
    }

    /**
    * Protège les requêtes contre l'injection
    */
    public function escape_string($argument) {
        return $this->dbconnector->connection->quote($argument);
    }


    /**
    * Méthode de récupération d'un opérateur défini dans la valeur d'un filtre avant réquête
    * @author Simon Alberny
    * @param <str> Opérateur (ex. : '=', '!=', '<', '<=', '>', '>=')
    * @param <str> Valeur ou opérateur valeur (ex. : '1', '< 1')
    * @return <array> 0: opérateur, 1: valeur
    */
    protected function getCustomQueryOperator($operation_default, $value) {
        $valid_operators = array('=','!=','<','<=','>','>=');
        $operation = $operation_default;

        // Modification de l'opération si contenu dans la valeur du filtre
        $value_list = explode(' ', $value);
        if((count($value_list) > 0) && (in_array($value_list[0],$valid_operators))) {
            $operation = $value_list[0];
            $value = $value_list[1];
        }

        return array($operation, $value);
    }

    /**
    * Définition des clauses du WHERE dans une requête à la base de données
    * @author Simon Alberny
    * @param <array> Tableau de correspondance colonne => valeur (ex. : array( 'column' => 'value', 'column2' => '!= 2' ) )
    * @param <str> Opérateur (ex. : '=', '!=', '<', '<=', '>', '>=')
    * @return <str> WHERE...
    */
    protected function getWhereClause($columns,$operation) {
        $whereClause = '';
        $operation_default = $operation;

        if($columns!=null && sizeof($columns)!=0){
            $whereClause .= ' WHERE ';
            $i = false;
            foreach($columns as $column=>$value){
                $customQueryOperator = $this->getCustomQueryOperator($operation_default, $value);
                if($i){$whereClause .=' AND ';}else{$i=true;}
                $whereClause .= '`'.$column.'`'.$customQueryOperator[0].'?';
                $this->prepareValue($customQueryOperator[1], $column);
            }
        }

        return $whereClause;
    }

    public function error() {
        return $this->dbconnector->error();
    }

}
?>
