<?php

/*
 @nom: Folder
 @auteur: Idleman (idleman@idleman.fr)
 @description: Classe de gestion des dossiers/catégories contenant les flux
 */

class Folder extends MysqlEntity{

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

	function unreadCount(){
		$prefixTable = $this->getPrefixTable();
		$results = $this->customQuery('SELECT COUNT('.$prefixTable.'event.id) FROM '.$prefixTable.'event INNER JOIN '.$prefixTable.'feed ON ('.$prefixTable.'event.feed = '.$prefixTable.'feed.id) WHERE '.$prefixTable.'event.unread=1 AND '.$prefixTable.'feed.folder = '.$this->getId());
		$number = mysql_fetch_array($results);
		return $number[0];
	}


	function getEvents($start=0,$limit=10000,$order,$columns='*'){
		$prefixTable = $this->getPrefixTable();
		$eventManager = new Event();
		$objects = array();
		$results = $this->customQuery('SELECT '.$columns.' FROM '.$prefixTable.'event INNER JOIN '.$prefixTable.'feed ON ('.$prefixTable.'event.feed = '.$prefixTable.'feed.id) WHERE '.$prefixTable.'event.unread=1 AND '.$prefixTable.'feed.folder = '.$this->getId().' ORDER BY '.$order.' LIMIT '.$start.','.$limit);
		if($results!=false){
			while($item = mysql_fetch_array($results)){
				$object = new Event();
					foreach($object->getObject_fields() as $field=>$type){
						$setter = 'set'.ucFirst($field);
						if(isset($item[$field])) $object->$setter($item[$field]);
					}
					$objects[] = $object;
					unset($object);
			}
		}
		
		return $objects;
	}

	function __construct(){
		parent::__construct();
		$myUser = (isset($_SESSION['currentUser'])?unserialize($_SESSION['currentUser']):false);
		if ($myUser!=false) { $this->setPrefixTable($myUser->getPrefixDatabase()); }
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
