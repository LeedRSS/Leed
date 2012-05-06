<?php
class Configuration extends SQLiteEntity{

	protected $id,$key,$value;
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


	public function get($key){
		$configurationManager = new Configuration();
		$config = $configurationManager->load(array('key'=>$key));
		return (is_object($config)?$config->getValue():'');
	}

	public function put($key,$value){
		$configurationManager = new Configuration();
		$config = $configurationManager->load(array('key'=>$key));
		$config = (!$config?new Configuration():$config);
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