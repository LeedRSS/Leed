<?php
class Folder extends SQLiteEntity{

	protected $id,$name,$parent,$isopen;
	protected $TABLE_NAME = 'folder';
	protected $CLASS_NAME = 'Folder';
	protected $object_fields = 
	array(
		'id'=>'key',
		'name'=>'string',
		'parent'=>'integer',
		'isopen'=>'integer'
	);

	function __construct(){
		parent::__construct();
	}

	function setId($id){
		$this->id = $id;
	}

	function getFeeds(){
		$feedManager = new Feed();
		return $feedManager->loadAll(array('folder'=>$this->getId()),'name');
	}

	function getFolders(){
		$folderManager = new Folder();
		return $folderManager->loadAll(array('parent'=>$this->getId()));
	}


	function getId(){
		return $this->id;
	}

	function getName(){
		return $this->name;
	}

	function setName($name){
		$this->name = $name;
	}

	function getParent(){
		return $this->parent;
	}

	function setParent($parent){
		$this->parent = $parent;
	}

	function getIsopen(){
		return $this->isopen;
	}

	function setIsopen($isopen){
		$this->isopen = $isopen;
	}



}

?>