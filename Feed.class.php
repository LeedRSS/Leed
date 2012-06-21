<?php

/*
 @nom: Feed
 @auteur: Idleman (idleman@idleman.fr)
 @description: Classe de gestion des flux RSS/ATOM
 */

class Feed extends MysqlEntity{

	protected $id,$name,$url,$events=array(),$description,$website,$folder,$lastupdate;
	protected $TABLE_NAME = 'feed';
	protected $CLASS_NAME = 'Feed';
	protected $object_fields = 
	array(
		'id'=>'key',
		'name'=>'string',
		'description'=>'longstring',
		'website'=>'longstring',
		'url'=>'longstring',
		'lastupdate'=>'string',
		'folder'=>'integer'
	);

	function __construct($name=null,$url=null){
		$this->name = $name;
		$this->url = $url;
		parent::__construct();
	}

	function getInfos(){
		$xml = @simplexml_load_file($this->url);
		if($xml!=false){
			$this->name = array_shift ($xml->xpath('channel/title'));
			$this->description = array_shift ($xml->xpath('channel/description'));
			$this->website = array_shift ($xml->xpath('channel/link'));
		}
	}

	function parse(){

		
		$feed = new SimplePie();
		$feed->set_feed_url($this->url);
		$feed->init();
		$feed->handle_content_type();

		if($this->name=='') $this->name = $feed->get_title();
		if($this->name=='') $this->name = $this->url;
		$this->website = $feed->get_link();
		$this->description = $feed->get_description();

		$items = $feed->get_items();
		$eventManager = new Event();
			
		$nonParsedEvents = array();
		foreach($items as $item){

				//Deffinition du GUID : 
			
				$alreadyParsed = $eventManager->rowCount(array('guid'=>htmlentities($item->get_id())));
				

				if($alreadyParsed==0){
					$event = new Event();
					$event->setGuid($item->get_id());
					$event->setTitle($item->get_title());
					$event->setPubdate($item->get_date());
					$event->setCreator( (is_object($item->get_author())?$item->get_author()->name:'Anonyme') );
				
					$event->setLink($item->get_permalink());
					$event->setContent($item->get_content());
					$event->setDescription($item->get_description());
					
					if(trim($event->getDescription())=='')
						$event->setDescription(substr($event->getContent(),0,300).'...<br><a href="'.$event->getLink().'">Lire la suite de l\'article</a>');
					
					if(trim($event->getContent())=='')
						$event->setContent($event->getDescription());
					
					$event->setCategory($item->get_category());
					$event->setFeed($this->id);
					$event->setUnread(1);
					$nonParsedEvents[] = $event;
					unset($event);
				}

		}

			if(count($nonParsedEvents)!=0) $eventManager->massiveInsert($nonParsedEvents);

			$result = true;
				
		
			$this->lastupdate = $_SERVER['REQUEST_TIME'];
			$this->save();
			return $result;
	}


	function removeOldEvents($maxEvent){
		$eventManager = new Event();
		$limit = $eventManager->rowCount(array('feed'=>$this->id))-$maxEvent;
		if ($limit>0) $this->customExecute("DELETE FROM Event WHERE id in(SELECT id FROM Event WHERE feed=".$this->id."  AND favorite!=1 ORDER BY pubDate ASC LIMIT ".($limit>0?$limit:0).");");
	}
	
	function setId($id){
		$this->id = $id;
	}

	function getDescription(){
		return stripslashes($this->description);
	}

	function setDescription($description){
		$this->description = html_entity_decode($description);
	}
	function getWebSite(){
		return $this->website;
	}

	function setWebSite($website){
		$this->website = $website;
	}

	function getId(){
		return $this->id;
	}

	function getUrl(){
		return $this->url;
	}

	function setUrl($url){
		$this->url = $url;
	}

	function getName(){
		return (trim($this->name)!='' ? $this->name:$this->url);
	}

	function setName($name){
		$this->name = html_entity_decode($name);
	}


	function getEvents($start=0,$limit=10000,$order,$columns='*'){
		$eventManager = new Event();
		$events = $eventManager->loadAllOnlyColumn($columns,array('feed'=>$this->getId()),$order,$start.','.$limit);
		return $events;
	}

	function countUnreadEvents(){
		$unreads = array();
		$results = Feed::customQuery("SELECT COUNT(event.id), feed.id FROM event INNER JOIN feed ON (event.feed = feed.id) WHERE event.unread = '1' GROUP BY feed.id") ;
		while($item = mysql_fetch_array($results)){
			$unreads[$item[1]] = $item[0];
		}
		return $unreads;
	}

	function getFeedsPerFolder(){
		$feeds = array();
		$results = Feed::customQuery("SELECT feed.name AS name, feed.id   AS id, feed.url  AS url, folder.id AS folder FROM feed INNER JOIN folder ON ( feed.folder = folder.id ) ORDER BY feed.name ;");
		while($item = mysql_fetch_array($results)){
			$feeds[$item['folder']][$item['id']]['id'] = $item['id'];
			$feeds[$item['folder']][$item['id']]['name'] = html_entity_decode($item['name']);
			$feeds[$item['folder']][$item['id']]['url'] = $item['url'];
		}
		return $feeds;
	}


	function getFolder(){
		return $this->folder;
	}

	function setFolder($folder){
		$this->folder = $folder;
	}

	function getLastupdate(){
		return $this->lastUpdate;
	}

	function setLastupdate($lastupdate){
		$this->lastupdate = $lastupdate;
	}
	


}

?>