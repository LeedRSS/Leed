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

		
	function sgbdType($type){
		$return = false;
		switch($type){
			case 'string':
			case 'timestamp':
				$return = 'VARCHAR(225)';
			break;
			case 'longstring':
				$return = 'TEXT';
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
				$return = 'TEXT';
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
	* @param <String> $debug='false' active le debug mode (0 ou 1)
	* @return Aucun retour
	*/
	public function destroy($debug='false')
	{
		$query = 'DROP TABLE IF EXISTS '.MYSQL_PREFIX.$this->TABLE_NAME.';';
		if($this->debug)echo '<hr>'.$this->CLASS_NAME.' ('.__METHOD__ .') : Requete --> '.$query.'<br>'.mysql_error();
		$myQuery = mysql_query($query) or die(mysql_error());
	}

	/**
	* Methode de nettoyage de l'entité
	* @author Valentin CARRUESCO
	* @category manipulation SQL
	* @param <String> $debug='false' active le debug mode (0 ou 1)
	* @return Aucun retour
	*/
	public function truncate($debug='false')
	{
			$query = 'TRUNCATE TABLE '.MYSQL_PREFIX.$this->TABLE_NAME.';';
			if($this->debug)echo '<hr>'.$this->CLASS_NAME.' ('.__METHOD__ .') : Requete --> '.$query.'<br>'.mysql_error();
			$myQuery = mysql_query($query) or die(mysql_error());
	}

	/**
	* Methode de creation de l'entité
	* @author Valentin CARRUESCO
	* @category manipulation SQL
	* @param <String> $debug='false' active le debug mode (0 ou 1)
	* @return Aucun retour
	*/
	public function create($debug='false'){
		$query = 'CREATE TABLE IF NOT EXISTS `'.MYSQL_PREFIX.$this->TABLE_NAME.'` (';

		$i=false;
		foreach($this->object_fields as $field=>$type){
			if($i){$query .=',';}else{$i=true;}
			$query .='`'.$field.'`  '. $this->sgbdType($type).'  NOT NULL';
			
		}

		$query .= ');';
		if($this->debug)echo '<hr>'.$this->CLASS_NAME.' ('.__METHOD__ .') : Requete --> '.$query.'<br>'.mysql_error();
		$myQuery = mysql_query($query) or die(mysql_error());
	}



	public function massiveInsert($events){
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
						$query .='"'.eval('return htmlentities($event->'.$field.');').'"';
					}
				}
				
			}

			$query .=';';
		//echo '<i>'.$this->CLASS_NAME.' ('.__METHOD__ .') : Requete --> '.$query.'<br>';
		mysql_query($query) or die(mysql_error());

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
				$id = eval('return htmlentities($this->'.$field.');');
				$query .= '`'.$field.'`="'.$id.'"';
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
				$query .='"'.eval('return htmlentities($this->'.$field.');').'"';
			}

			$query .=');';
		}
		//echo '<hr>'.$this->CLASS_NAME.' ('.__METHOD__ .') : Requete --> '.$query.'<br>'.mysql_error();
		mysql_query($query)or die(mysql_error());
		$this->id =  (!isset($this->id)?mysql_insert_id():$this->id);
	}

	/**
	* Méthode de modification d'éléments de l'entité
	* @author Valentin CARRUESCO
	* @category manipulation SQL
	* @param <Array> $colonnes=>$valeurs
	* @param <Array> $colonnes (WHERE) =>$valeurs (WHERE)
	* @param <String> $operation="=" definis le type d'operateur pour la requete select
	* @param <String> $debug='false' active le debug mode (0 ou 1)
	* @return Aucun retour
	*/
	public function change($columns,$columns2,$operation='=',$debug='false'){
		$query = 'UPDATE `'.MYSQL_PREFIX.$this->TABLE_NAME.'` SET ';
		$i=false;
		foreach ($columns as $column=>$value){
			if($i){$query .=',';}else{$i=true;}
			$query .= '`'.$column.'`="'.$value.'" ';
		}
		$query .=' WHERE '; 

		$i = false;
		foreach ($columns2 as $column=>$value){
			if($i){$query .='AND ';}else{$i=true;}
			$query .= '`'.$column.'`'.$operation.'"'.$value.'" ';
			
		}
		//echo '<hr>'.$this->CLASS_NAME.' ('.__METHOD__ .') : Requete --> '.$query.'<br>'.mysql_error();
		mysql_query($query)or die(mysql_error());
	}

	/**
	* Méthode de selection de tous les elements de l'entité
	* @author Valentin CARRUESCO
	* @category manipulation SQL
	* @param <String> $ordre=null
	* @param <String> $limite=null
	* @param <String> $debug='false' active le debug mode (0 ou 1)
	* @return <Array<Entity>> $Entity
	*/
	public function populate($order='null',$limit='null',$debug='false'){
		eval('$results = '.$this->CLASS_NAME.'::loadAll(array(),"'.$order.'",'.$limit.',\'=\','.$debug.');');
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
	* @param <String> $debug='false' active le debug mode (0 ou 1)
	* @return <Array<Entity>> $Entity
	*/
	public function loadAll($columns,$order=null,$limit=null,$operation="=",$debug='false',$selColumn='*'){
		$objects = array();
		$whereClause = '';
	
			if($columns!=null && sizeof($columns)!=0){
			$whereClause .= ' WHERE ';
				$i = false;
				foreach($columns as $column=>$value){

					if($i){$whereClause .=' AND ';}else{$i=true;}
					$whereClause .= '`'.$column.'`'.$operation.'"'.$value.'"';
				}
			}
			$query = 'SELECT '.$selColumn.' FROM `'.MYSQL_PREFIX.$this->TABLE_NAME.'` '.$whereClause.' ';
			if($order!=null) $query .='ORDER BY '.$order.' ';
			if($limit!=null) $query .='LIMIT '.$limit.' ';
			$query .=';';

			// echo '<br>'.__METHOD__.' : Requete --> '.$query.'<br>';
			$execQuery = mysql_query($query) or die(mysql_error());
			while($queryReturn = mysql_fetch_assoc($execQuery)){

				$object = eval(' return new '.$this->CLASS_NAME.'();');
				foreach($this->object_fields as $field=>$type){
					if(isset($queryReturn[$field])) eval('$object->'.$field .'= html_entity_decode(\''. addslashes($queryReturn[$field]).'\');');
				}
				$objects[] = $object;
				unset($object);
			}
			return $objects;
	}

		public function loadAllOnlyColumn($selColumn,$columns,$order=null,$limit=null,$operation="=",$debug='false'){
		eval('$objects = $this->loadAll($columns,\''.$order.'\',\''.$limit.'\',\''.$operation.'\',\''.$debug.'\',\''.$selColumn.'\');');
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
	* @param <String> $debug='false' active le debug mode (0 ou 1)
	* @return <Entity> $Entity ou false si aucun objet n'est trouvé en base
	*/
	public function load($columns,$operation='=',$debug='false'){
		eval('$objects = $this->loadAll($columns,null,\'1\',\''.$operation.'\',\''.$debug.'\');');
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
	* @param <String> $debug='false' active le debug mode (0 ou 1)
	* @return <Entity> $Entity ou false si aucun objet n'est trouvé en base
	*/
	public function getById($id,$operation='=',$debug='false'){
		return $this->load(array('id'=>$id),$operation,$debug);
	}

	/**
	* Methode de comptage des éléments de l'entité
	* @author Valentin CARRUESCO
	* @category manipulation SQL
	* @param <String> $debug='false' active le debug mode (0 ou 1)
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
					$whereClause .= '`'.$column.'`="'.$value.'"';
			}
		}
		$query = 'SELECT COUNT(id) FROM '.MYSQL_PREFIX.$this->TABLE_NAME.$whereClause;
		if($this->debug)echo '<hr>'.$this->CLASS_NAME.' ('.__METHOD__ .') : Requete --> '.$query.'<br>'.mysql_error();
		$myQuery = mysql_query($query) or die(mysql_error());
		$number = mysql_fetch_array($myQuery);
		return $number[0];
	}	
	
	/**
	* Méthode de supression d'elements de l'entité
	* @author Valentin CARRUESCO
	* @category manipulation SQL
	* @param <Array> $colonnes (WHERE)
	* @param <Array> $valeurs (WHERE)
	* @param <String> $operation="=" definis le type d'operateur pour la requete select
	* @param <String> $debug='false' active le debug mode (0 ou 1)
	* @return Aucun retour
	*/
	public function delete($columns,$operation='=',$debug='false'){
		$whereClause = '';

			$i=false;
			foreach($columns as $column=>$value){
				if($i){$whereClause .=' AND ';}else{$i=true;}
				$whereClause .= '`'.$column.'`'.$operation.'"'.$value.'"';
			}
			$query = 'DELETE FROM `'.MYSQL_PREFIX.$this->TABLE_NAME.'` WHERE '.$whereClause.' ;';
			if($this->debug)echo '<hr>'.$this->CLASS_NAME.' ('.__METHOD__ .') : Requete --> '.$query.'<br>'.mysql_error();
			mysql_query($query);
		
	}

	public function customExecute($request){
		mysql_query($request);
	}
	public function customQuery($request){
		$result = mysql_query($request);
		//echo $request;
		//var_dump(mysql_error());
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

}
?>
