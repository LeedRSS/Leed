<?php

class Event extends SQLiteEntity{

	protected $id,$title,$guid,$content,$description,$pudate,$link,$feed,$category,$creator,$unread,$favorite;
	protected $TABLE_NAME = 'event';
	protected $CLASS_NAME = 'Event';
	protected $object_fields = 
	array(
		'id'=>'key',
		'guid'=>'longstring',
		'title'=>'string',
		'creator'=>'string',
		'content'=>'longstring',
		'description'=>'longstring',
		'unread'=>'integer',
		'feed'=>'integer',
		'unread'=>'integer',
		'favorite'=>'integer',
		'pubdate'=>'string'
	);

	function __construct($guid=null,$title=null,$description=null,$content=null,$pubdate=null,$link=null,$category=null,$creator=null){
		
		$this->guid = $guid;
		$this->title = $title;
		$this->creator = $creator;
		$this->content = $content;
		$this->description = $description;
		$this->pubdate = $pubdate;
		$this->link = $link;
		$this->category = $category;
		parent::__construct();
	}



	function getCreator(){
		return $this->creator;
	}

	function setCreator($creator){
		$this->creator = $creator;
	}

	function getCategory(){
		return $this->category;
	}

	function setCategory($category){
		$this->category = $category;
	}

	function getDescription(){
		return utf8_encode($this->description);
	}

	function setDescription($description){
		$this->description = $description;
	}

	function getPubdate(){
		return $this->pubdate;
	}

	function setPubdate($pubdate){
		$this->pubdate = date('d/m/Y H:i:s',strtotime($pubdate));
	}

	function getLink(){
		return $this->link;
	}

	function setLink($link){
		$this->link = $link;
	}

	function getId(){
		return $this->id;
	}

	function getTitle(){
		return $this->title;
	}

	function setTitle($title){
		$this->title = $title;
	}

	function getContent(){
		return $this->content;
	}

	function setContent($content){
		$this->content = $content;
	}


	function getGuid(){
		return $this->guid;
	}

	function setGuid($guid){
		$this->guid = $guid;
	}

	function getUnread(){
		return $this->unread;
	}

	function setUnread($unread){
		$this->unread = $unread;
	}
	function setFeed($feed){
		$this->feed = $feed;
	}
	function getFeed(){
		return $this->feed;
	}
	function setFavorite($favorite){
		$this->favorite = $favorite;
	}
	function getFavorite(){
		return $this->favorite;
	}

}

?>