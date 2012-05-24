<?php
class Feed extends SQLiteEntity{

	protected $id,$name,$url,$unread=0,$events=array(),$description,$website,$folder,$lastupdate;
	protected $TABLE_NAME = 'feed';
	protected $CLASS_NAME = 'Feed';
	protected $object_fields = 
	array(
		'id'=>'key',
		'name'=>'string',
		'description'=>'longstring',
		'website'=>'longstring',
		'url'=>'longstring',
		'unread'=>'integer',
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



		/*

		//TODO parser a travers un proxy

		 $proxy = getenv("HTTP_PROXY"); 
		
		 if (strlen($proxy) > 1) { 
		 $r_default_context = stream_context_get_default (array 
		                    ('http' => array( 
		                     'proxy' => $proxy, 
		                     'request_fulluri' => True, 
		                     'header'=>sprintf('Authorization: Basic %s',base64_encode('login:password'))
		                    ), 
		                ) 
		            ); 
		  libxml_set_streams_context($r_default_context); 
		      } 
		*/

		$xml = @simplexml_load_file($this->url,"SimpleXMLElement",LIBXML_NOCDATA);

		if(is_object($xml)){

			
			
			$this->name = (isset($xml->title)?$xml->title:$xml->channel->title);
			$this->description = $xml->channel->description;
			$this->website = $xml->channel->link;

			if(trim($this->website)=='') $this->website = (isset($xml->link[1]['href'])?$xml->link[1]['href']:$xml->link['href']);
			if(trim($this->description)=='') $this->description = $xml->subtitle;

			$eventManager = new Event();

			
			$items = $xml->xpath('//item');
		
			if(count($items)==0) $items = $xml->entry;

			

			foreach($items as $item){

				$alreadyParsed = $eventManager->load(array('guid'=>$item->guid));

				if(!$alreadyParsed){
					$event = new Event($item->guid,$item->title);
					$namespaces = $item->getNameSpaces(true);
					if(isset($namespaces['dc'])){ 
						$dc = $item->children($namespaces['dc']);
						$event->setCreator($dc->creator);
						$event->setPubdate($dc->date);

						if($event->getPubdate()=='')
						$event->setPubdate($dc->pubDate);

					}


					if(trim($event->getPubdate()==''))
						$event->setPubdate($item->pubDate);

					if(trim($event->getPubdate()==''))
						$event->setPubdate($item->date);

					if(trim($event->getPubdate()==''))
						$event->setPubdate($item->published);

					if(trim($event->getCreator()==''))
						$event->setCreator($item->creator);

					if(trim($event->getCreator()==''))
						$event->setCreator($item->author);

					if(trim($event->getGuid()==''))
						$event->setGuid($item->link['href']);
					
					
					$event->setDescription(utf8_decode($item->description));
				

					if(isset($namespaces['content'])){
						$event->setContent($item->children($namespaces['content']));
					
					}else if(isset($event->content)){
						$event->setContent($event->content);
					}else{
						/*//Tentative de detronquage si la description existe
						if($event->getDescription()!=''){
							  // preg_match('#<a(.+)href=(.+)>#isU', $event->getDescription(), $matches);
							 //echo var_dump($matches);

							//Récup de l'article dans son contexte
							 $allContent = simplexml_load_file($event->getGuid());
							 if($allContent!=false){
							 	foreach($xml->xpath('//item') as $div){
							 		echo var_dump($div);
							 		echo '<hr>';
							 	}
							 }
							

						}
						*/
					}
					$event->setLink($item->link);
					$event->setCategory($item->category);
					
					$event->setFeed($this->id);
					$event->setUnread(1);
					$event->save();

				}

			}
			$result = true;
				
		}else{
			$this->name = 'Flux invalide';
			$this->description = 'Impossible de se connecter au flux demand&eacute, peut &ecirc;tre est il en maintenance?';
			$result = false;
		}
			$this->lastupdate = time();
			$this->save();
			return $result;
	}

	


	function getDescription(){
		return stripslashes($this->description);
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

	function getUnread(){
		return $this->unread;
	}

	function getEvents($start=0,$limit=10000,$order){
		$eventManager = new Event();
		$events = $eventManager->loadAll(array('feed'=>$this->getId()),$order,$start.','.$limit);
		return $events;
	}

	function countUnreadEvents(){
		$result = 0;
		$eventManager = new Event();
		$result = $eventManager->rowCount(array('feed'=>$this->getId(),'unread'=>'1'));
		return $result;
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