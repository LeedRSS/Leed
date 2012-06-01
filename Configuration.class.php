<?php


/*
 @nom: Configuration
 @auteur: Idleman (idleman@idleman.fr)
 @description: Classe de gestion des préférences, fonctionne sur un simple système clé=>valeur avec un cache session pour eviter les requête inutiles
 */

class Configuration extends SQLiteEntity{

	protected $id,$key,$value,$confTab;
	protected $TABLE_NAME = 'configuration';
	protected $CLASS_NAME = 'Configuration';
	protected $object_fields = 
	array(
		'id'=>'key',
		'key'=>'longstring',
		'value'=>'longstring'
	);

	function __construct(){
		parent::__construct();
	}

	public function getAll(){

		if(!isset($_SESSION['configuration'])){
	
		$configurationManager = new Configuration();
		$configsQuery = $configurationManager->customQuery('SELECT key,value FROM configuration');
		$confTab = array();

		while($config = $configsQuery->fetchArray() ){
			$this->confTab[$config['key']] = $config['value'];
		}

		$_SESSION['configuration'] = serialize($this->confTab);
		
		}else{
			$this->confTab = unserialize($_SESSION['configuration']);
		}
	}

	public function get($key){
		return (isset($this->confTab[$key])?$this->confTab[$key]:'');
	}

	public function put($key,$value){
		$configurationManager = new Configuration();
		$configurationManager->change(array('value'=>$value),array('key'=>$key));
		$this->confTab[$key] = $value;
		unset($_SESSION['configuration']);
	}

	public function add($key,$value){
		$config = new Configuration();
		$config->setKey($key);
		$config->setValue($value);
		$config->save();
	}
	
	function getId(){
		return $this->id;
	}

	function getKey(){
		return $this->key;
	}

	function setKey($key){
		$this->key = $key;
	}

	function getValue(){
		return $this->value;
	}

	function setValue($value){
		$this->value = $value;
	}




}

?>