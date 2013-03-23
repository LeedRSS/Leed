<?php

/*
 @nom: Event
 @auteur: Idleman (idleman@idleman.fr)
 @description: Classe de gestion des évenements/news liés a chaques flux RSS/ATOM
 */

class Event extends MysqlEntity{

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
		'link'=>'longstring',
		'unread'=>'integer',
		'feed'=>'integer',
		'unread'=>'integer',
		'favorite'=>'integer',
		'pubdate'=>'integer'
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


	function getEventCountPerFolder(){
		$events = array();
		$results = $this->customQuery('SELECT COUNT('.MYSQL_PREFIX.$this->TABLE_NAME.'.id),'.MYSQL_PREFIX.'folder.id FROM '.MYSQL_PREFIX.$this->TABLE_NAME.' INNER JOIN '.MYSQL_PREFIX.'feed ON ('.MYSQL_PREFIX.'event.feed = '.MYSQL_PREFIX.'feed.id) INNER JOIN '.MYSQL_PREFIX.'folder ON ('.MYSQL_PREFIX.'folder.id = '.MYSQL_PREFIX.'feed.folder) WHERE '.MYSQL_PREFIX.$this->TABLE_NAME.'.unread=1 GROUP BY '.MYSQL_PREFIX.'folder.id');
		
		while($item = mysql_fetch_array($results)){
			$events[$item[1]] = $item[0];
		}
		
		return $events;
	}

	function setId($id){
		$this->id = $id;
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
		return $this->description;
	}

	function setDescription($description,$encoding = true){
		$this->description = $description;
	}

	function getPubdate($format=false){
		if($this->pubdate!=0){
		return ($format!=false?date($format,$this->pubdate):$this->pubdate);
		}else{
			return '';
		}
	}

	function getPubdateWithInstant($instant){
		$alpha = $instant - $this->pubdate;
		if ($alpha < 86400 ){
			$hour = floor($alpha/3600);
			$alpha = ($hour!=0?$alpha-($hour*3600):$alpha);
			$minuts = floor($alpha/60);
			return 'il y a '.($hour!=0?$hour.'h et':'').' '.$minuts.'min';
		}else{
			return 'le '.$this->getPubdate('d/m/Y à H:i:s');
		}
	}

	function setPubdate($pubdate){
		$this->pubdate = strtotime($pubdate);
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
		
		$tags_to_encode = array('code', 'pre');
		
		foreach ($tags_to_encode as $tag) {
			$this->parseTagEntities($tag, $tags_to_encode);
		}
		
		return $this->content; 
	}	
	
	function parseTagEntities($tag, $tags_to_ignore) {
		$segments = preg_split('/(<\/?' . $tag . '>)/', $this->content, -1, PREG_SPLIT_DELIM_CAPTURE);
		$depth = 0;
		foreach ($segments as &$segment) {
			if ($segment == '<' . $tag . '>') {
				$depth++;
			} else if ($depth > 0 && $segment == '</' . $tag . '>') {
				$depth--;
			}
			if (!in_array($segment, array('<' . $tag . '>', '</' . $tag . '>')) && $depth > 0) {
				
				// Gestion de l'imbrication des tags
				
				//Remplacement temporaire des tags ouvrant / fermant à ignorer
				foreach ($tags_to_ignore as $tag_ignored) {
					$segment = str_replace('<' . $tag_ignored . '>', '[[[' . $tag_ignored . ']]]', $segment);
					$segment = str_replace('</' . $tag_ignored . '>', '[[[/' . $tag_ignored . ']]]', $segment);
				}
				
				// Entités HTML sur les chevrons des balises situés dans le tag à parser
				$segment = str_replace(
					array('<', '>'), 
					array('&lt;', '&gt;'),
					$segment
				);
				
				// Rétablissement des tags ouvrant / fermant à ignorer
				foreach ($tags_to_ignore as $tag_ignored) {
					$segment = str_replace('[[[' . $tag_ignored . ']]]', '<' . $tag_ignored . '>', $segment);
					$segment = str_replace('[[[/' . $tag_ignored . ']]]', '</' . $tag_ignored . '>', $segment);
				}
			}
		}

		$this->content = implode($segments);
	}

	function setContent($content,$encoding=true){
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
