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

		$feed->set_useragent('Mozilla/4.0 Leed (LightFeed Agrgegator) '.VERSION_NAME.' by idleman http://projet.idleman.fr/leed');

		$feed->init();
		$feed->handle_content_type();
		

		if($this->name=='') $this->name = $feed->get_title();
		if($this->name=='') $this->name = $this->url;
		$this->website = $feed->get_link();
		$this->description = $feed->get_description();

		$items = $feed->get_items();
		$eventManager = new Event();
			
		$nonParsedEvents = array();
		$iEvents = 0;
		foreach($items as $item){
			
				//Definition du GUID : 
			
				$alreadyParsed = $eventManager->rowCount(array('guid'=>$this->secure($item->get_id(), 'guid')));
				
				if($alreadyParsed==0 && $iEvents<100){
					$event = new Event();
					$event->setGuid($item->get_id());
					$event->setTitle(html_entity_decode($item->get_title(), ENT_COMPAT, 'UTF-8'));
					$event->setPubdate($item->get_date());
					$event->setCreator( (is_object($item->get_author())?$item->get_author()->name:'Anonyme') );
				
					$event->setLink($item->get_permalink());
					

					//Gestion de la balise enclosure pour les podcasts et autre cochonneries :)
					$enclosure = $item->get_enclosure(); 
					if($enclosure!=null && $enclosure->link!=''){
						
						$enclosureName = substr($enclosure->link,strrpos($enclosure->link, '/')+1,strlen($enclosure->link));
						$enclosureArgs = strpos($enclosureName, '?');
						if($enclosureArgs!==false) $enclosureName = substr($enclosureName,0,$enclosureArgs);
						$enclosureFormat = (isset($enclosure->handler)?$enclosure->handler:substr($enclosureName,strrpos($enclosureName,'.')+1));
					
						$enclosure ='<div class="enclosure"><h1>Fichier média :</h1><a href="'.$enclosure->link.'"> '.$enclosureName.'</a> <span>(Format '.strtoupper($enclosureFormat).', '.Functions::convertFileSize($enclosure->length).')</span>';
					}else{
						$enclosure = '';
					}



					$event->setContent(html_entity_decode($item->get_content(), ENT_COMPAT, 'UTF-8').html_entity_decode($enclosure, ENT_COMPAT, 'UTF-8'));
					$event->setDescription(html_entity_decode($item->get_description(), ENT_COMPAT, 'UTF-8').html_entity_decode($enclosure, ENT_COMPAT, 'UTF-8'));
					
				
					if(trim($event->getDescription())=='')
						$event->setDescription(substr($event->getContent(),0,300).'...<br><a href="'.$event->getLink().'">Lire la suite de l\'article</a>');
					
					if(trim($event->getContent())=='')
						$event->setContent($event->getDescription());
					
					$event->setCategory($item->get_category());
					$event->setFeed($this->id);
					$event->setUnread(1);
					$nonParsedEvents[] = $event;
					unset($event);
					$iEvents++;
				}
			
			
		}

			if(count($nonParsedEvents)!=0) {

				$eventManager->massiveInsert($nonParsedEvents);
			}

			$result = true;
				
		
			$this->lastupdate = $_SERVER['REQUEST_TIME'];
			$this->save();
			return $result;
	}


	function removeOldEvents($maxEvent){
		$eventManager = new Event();
		$limit = $eventManager->rowCount(array('feed'=>$this->id))-$maxEvent;
		if ($limit>0) $this->customExecute("DELETE FROM ".MYSQL_PREFIX."event WHERE id in(SELECT id FROM ".MYSQL_PREFIX."event WHERE feed=".$this->id."  AND favorite!=1 ORDER BY pubDate ASC LIMIT ".($limit>0?$limit:0).");");
	}
	
	function setId($id){
		$this->id = $id;
	}

	function getDescription(){
		return $this->description;
	}

	function setDescription($description){
		$this->description = $description;
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
		$this->name = $name;
	}


	function getEvents($start=0,$limit=10000,$order,$columns='*'){
		$eventManager = new Event();
		$events = $eventManager->loadAllOnlyColumn($columns,array('feed'=>$this->getId()),$order,$start.','.$limit);
		return $events;
	}

	function countUnreadEvents(){
		$unreads = array();
		$results = Feed::customQuery("SELECT COUNT(".MYSQL_PREFIX."event.id), ".MYSQL_PREFIX."feed.id FROM ".MYSQL_PREFIX."event INNER JOIN ".MYSQL_PREFIX."feed ON (".MYSQL_PREFIX."event.feed = ".MYSQL_PREFIX."feed.id) WHERE ".MYSQL_PREFIX."event.unread = '1' GROUP BY ".MYSQL_PREFIX."feed.id") ;
		if($results!=false){
			while($item = mysql_fetch_array($results)){
				$unreads[$item[1]] = $item[0];
			}
		}
		return $unreads;
	}

	function getFeedsPerFolder(){
		$feedsFolderMap = array();
		$feedsIdMap = array();

		$results = Feed::customQuery("SELECT ".MYSQL_PREFIX."feed.name AS name, ".MYSQL_PREFIX."feed.id   AS id, ".MYSQL_PREFIX."feed.url  AS url, ".MYSQL_PREFIX."folder.id AS folder FROM ".MYSQL_PREFIX."feed INNER JOIN ".MYSQL_PREFIX."folder ON ( ".MYSQL_PREFIX."feed.folder = ".MYSQL_PREFIX."folder.id ) ORDER BY ".MYSQL_PREFIX."feed.name ;");
		if($results!=false){
			while($item = mysql_fetch_array($results)){
				$name = $item['name'];
				$feedsIdMap[$item['id']]['name'] = $name;
				

				$feedsFolderMap[$item['folder']][$item['id']]['id'] = $item['id'];
				$feedsFolderMap[$item['folder']][$item['id']]['name'] = $name;
				$feedsFolderMap[$item['folder']][$item['id']]['url'] = $item['url'];
				
			}
		}
		$feeds['folderMap'] = $feedsFolderMap;
		$feeds['idMap'] = $feedsIdMap;
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
