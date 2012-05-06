<?php

/*
	@nom: SQLiteEntity
	@auteur: Valentin CARRUESCO (valentincarruesco@yahoo.fr)
	@date de création: 16/04/2012 02:34:15
	@description: Classe parent de tous les modèles (classe entitées) liées a la base de donnée,
	 cette classe est configuré pour agir avec une base SQLite, mais il est possible de redefinir ses codes SQL pour l'adapter à un autre SGBD sans affecter 
	 le reste du code du projet.

*/

class SQLiteEntity extends SQLite3
{
	
	private $debug = false;
	



	function __construct(){
		$this->open('database.db');
	}

	function __destruct(){
		 $this->close();
	}

	function sgbdType($type){
		$return = false;
		switch($type){
			case 'string':
			case 'timestamp':
			case 'date':
				$return = 'VARCHAR(255)';
			break;
			case 'longstring':
				$return = 'longtext';
			break;
			case 'key':
				$return = 'INTEGER NOT NULL PRIMARY KEY';
			break;
			case 'object':
			case 'integer':
				$return = 'bigint(20)';
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
	

	public function closeDatabase(){
		$this->close();
	}


	// GESTION SQL



	/**
	* Methode de creation de l'entité
	* @author Valentin CARRUESCO
	* @category manipulation SQL
	* @param <String> $debug='false' active le debug mode (0 ou 1)
	* @return Aucun retour
	*/
	public function create($debug='false'){
		$query = 'CREATE TABLE IF NOT EXISTS `'.$this->TABLE_NAME.'` (';

		$end = end(array_keys($this->object_fields));
		foreach($this->object_fields as $field=>$type){
			$query .='`'.$field.'`  '. $this->sgbdType($type).'  NOT NULL';
			if($field != $end)$query .=',';
		}

		$query .= ');';
		if($this->debug)echo '<hr>'.$this->CLASS_NAME.' ('.__METHOD__ .') : Requete --> '.$query;
		if(!$this->exec($query)) echo $this->lastErrorMsg();
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
			$query = 'UPDATE `'.$this->TABLE_NAME.'`';
			$query .= ' SET ';

			$end = end(array_keys($this->object_fields));
			foreach($this->object_fields as $field=>$type){
				$id = eval('return $this->'.$field.';');
				$query .= '`'.$field.'`="'.$id.'"';
				if($field != $end)$query .=',';
			}

			$query .= ' WHERE `id`="'.$this->id.'";';
		}else{
			$query = 'INSERT INTO `'.$this->TABLE_NAME.'`(';
			$end = end(array_keys($this->object_fields));
			foreach($this->object_fields as $field=>$type){
				if($type!='key'){
					$query .='`'.$field.'`';
					if($field != $end)$query .=',';
				}
			}
			$query .=')VALUES(';
			$end = end(array_keys($this->object_fields));
			foreach($this->object_fields as $field=>$type){
				if($type!='key'){
					$query .='"'.eval('return htmlentities($this->'.$field.');').'"';
					if($field != $end)$query .=',';
				}
			}

			$query .=');';
		}
		if($this->debug)echo '<i>'.$this->CLASS_NAME.' ('.__METHOD__ .') : Requete --> '.$query.'<br>';
		if(!$this->exec($query)) echo $this->lastErrorMsg().'</i>';
		$this->id =  (!isset($this->id)?$this->lastInsertRowID():$this->id);
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
	public function change($columns,$columns2=null,$operation='=',$debug='false'){
		$query = 'UPDATE `'.$this->TABLE_NAME.'` SET ';
		$end = end(array_keys($columns));
		foreach ($columns as $column=>$value){
			$query .= '`'.$column.'`="'.$value.'" ';
			if($column != $end)$query .=',';
		}

		if($columns2!=null){
			$query .=' WHERE '; 
			$end = end(array_keys($columns2));
			foreach ($columns2 as $column=>$value){
				$query .= '`'.$column.'`'.$operation.'"'.$value.'" ';
				if($column != $end)$query .='AND ';
			}
		}

		if($this->debug)echo '<hr>'.$this->CLASS_NAME.' ('.__METHOD__ .') : Requete --> '.$query.'<br>';
		if(!$this->exec($query)) echo $this->lastErrorMsg();
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
		eval('$results = '.$this->CLASS_NAME.'::loadAll(array(),\''.$order.'\','.$limit.',\'=\','.$debug.');');
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
	public function loadAll($columns,$order=null,$limit=null,$operation="=",$debug='false'){
		$objects = array();
		$whereClause = '';
	
			if(sizeof($columns)!=0){
			$whereClause .= ' WHERE ';
				$start = reset(array_keys($columns));
				foreach($columns as $column=>$value){
					if($column != $start)$whereClause .= ' AND ';
					$whereClause .= '`'.$column.'`'.$operation.'"'.$value.'"';
				}
			}
			$query = 'SELECT * FROM `'.$this->TABLE_NAME.'` '.$whereClause.' ';
			if($order!=null) $query .='ORDER BY `'.$order.'` ';
			if($limit!=null) $query .='LIMIT '.$limit.' ';
			$query .=';';
			if($this->debug) echo '<br>'.__METHOD__.' : Requete --> '.$query.'<br>';
			

			$execQuery = $this->query($query);

			if(!$execQuery) echo $this->lastErrorMsg();

			while($queryReturn = $execQuery->fetchArray() ){

				$object = eval(' return new '.$this->CLASS_NAME.'();');
				foreach($this->object_fields as $field=>$type){

					eval('$object->'.$field .'= html_entity_decode(\''. addslashes($queryReturn[$field]).'\');');
				}
				$objects[] = $object;
				unset($object);
			}
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
			$start = reset(array_keys($columns));
			foreach($columns as $column=>$value){
					if($column != $start)$whereClause .= ' AND ';
					$whereClause .= '`'.$column.'`="'.$value.'"';
			}
		}
		$query = 'SELECT COUNT(id) FROM '.$this->TABLE_NAME.$whereClause;
		//echo '<hr>'.$this->CLASS_NAME.' ('.__METHOD__ .') : Requete --> '.$query.'<br>';
		$execQuery = $this->querySingle($query);
		//echo $this->lastErrorMsg();
		return (!$execQuery?0:$execQuery);
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

			$start = reset(array_keys($columns));
			foreach($columns as $column=>$value){
				if($column != $start)$whereClause .= ' AND ';
				$whereClause .= '`'.$column.'`'.$operation.'"'.$value.'"';
			}
			$query = 'DELETE FROM `'.$this->TABLE_NAME.'` WHERE '.$whereClause.' ;';
			if($this->debug)echo '<hr>'.$this->CLASS_NAME.' ('.__METHOD__ .') : Requete --> '.$query.'<br>';
			if(!$this->exec($query)) echo $this->lastErrorMsg();
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

}
?>
