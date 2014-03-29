<?php

/*
 @nom: Feed
 @auteur: Idleman (idleman@idleman.fr)
 @description: Classe de gestion des flux RSS/ATOM
 */

class Feed extends MysqlEntity{

    protected $id,$name,$url,$events=array(),$description,$website,$folder,$lastupdate,$isverbose;
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
        'folder'=>'integer',
        'isverbose'=>'boolean',
    );

    protected $object_fields_index =
    array(
        'folder'=>'index'
    );

    protected $error = '';

    function __construct($name=null,$url=null){
        $this->name = $name;
        $this->url = $url;
        parent::__construct();
    }

    /** @TODO: ne faire qu'un seul chargement avec SimplePie et récupérer les
    même informations. Mettre le chargement en cache au moins d'une méthode
    loadLeed() qui ne chargera qu'une seule fois. Voire même en déclenchement
    retardé, au dernier moment. */
    function getInfos(){
        $xml = @simplexml_load_file($this->url);
        if($xml!=false){
            $this->name = array_shift ($xml->xpath('channel/title'));
            $this->description = array_shift ($xml->xpath('channel/description'));
            $this->website = array_shift ($xml->xpath('channel/link'));
        }
    }

    function getError() { return $this->error; }

    /*@TODO: fournir un extrait quand il 'y a pas de description. De même pour les médias.
    @TODO: SimplePie remplace "é" par "&eacute;", il ne devrait pas le faire.
    J'ai testé set_stupidly_fast(true) sans succès.
    Il encadre les descriptions avec <div>, absents dans le source du flux.
    @TODO: la vérification de doublon est sous la responsabilité de l'appelant.
    Il serait peut-être utile de faire une méthode add() qui vérifie, plante si
    nécessaire, et appelle parse(). Impossible de vérifier dans parse() même
    car elle est appelée aussi pour autre chose que l'ajout.
    */
    function parse($syncId,&$nbEvents =0, $enableCache=true, $forceFeed=false){
        $nbEvents = 0;
        assert('is_int($syncId) && $syncId>0');
        if (empty($this->id) || 0 == $this->id) {
            /* Le flux ne dispose pas pas d'id !. Ça arrive si on appelle
            parse() sans avoir appelé save() pour un nouveau flux.
            @TODO: un create() pour un nouveau flux ? */
            $msg = 'Empty or null id for a feed! '
                  .'See '.__FILE__.' on line '.__LINE__;
            error_log($msg, E_USER_ERROR);
            die($msg); // Arrêt, sinon création événements sans flux associé.
        }
        $feed = new SimplePie();
        $feed->enable_cache($enableCache);
        $feed->force_feed($forceFeed);
        $feed->set_feed_url($this->url);
        $feed->set_useragent('Mozilla/4.0 Leed (LightFeed Aggregator) '.VERSION_NAME.' by idleman http://projet.idleman.fr/leed');
        if (!$feed->init()) {
            $this->error = $feed->error;
            $this->lastupdate = $_SERVER['REQUEST_TIME'];
            $this->save();
            return false;
        }

        $feed->handle_content_type(); // UTF-8 par défaut pour SimplePie

        if($this->name=='') $this->name = $feed->get_title();
        if($this->name=='') $this->name = $this->url;
        $this->website = $feed->get_link();
        $this->description = $feed->get_description();

        $items = $feed->get_items();
        $eventManager = new Event();

        $events = array();
        $iEvents = 0;
        foreach($items as $item){
            // Ne retient que les 100 premiers éléments de flux.
            if ($iEvents++>=100) break;

            // Si le guid existe déjà, on évite de le reparcourir.
            $alreadyParsed = $eventManager->load(array('guid'=>$item->get_id(), 'feed'=>$this->id));
            if (isset($alreadyParsed)&&($alreadyParsed!=false)) {
                $events[]=$alreadyParsed->getId();
                continue;
            }

            // Initialisation des informations de l'événement (élt. de flux)
            $event = new Event();
            $event->setSyncId($syncId);
            $event->setGuid($item->get_id());
            $event->setTitle($item->get_title());
            $event->setPubdate($item->get_date());
            $event->setCreator(
                ''==$item->get_author()
                    ? ''
                    : $item->get_author()->name
            );
            $event->setLink($item->get_permalink());

            $event->setFeed($this->id);
            $event->setUnread(1); // inexistant, donc non-lu

            //Gestion de la balise enclosure pour les podcasts et autre cochonneries :)
            $enclosure = $item->get_enclosure();
            if($enclosure!=null && $enclosure->link!=''){
                $enclosureName = substr(
                    $enclosure->link,
                    strrpos($enclosure->link, '/')+1,
                    strlen($enclosure->link)
                );
                $enclosureArgs = strpos($enclosureName, '?');
                if($enclosureArgs!==false)
                    $enclosureName = substr($enclosureName,0,$enclosureArgs);
                $enclosureFormat = isset($enclosure->handler)
                    ? $enclosure->handler
                    : substr($enclosureName, strrpos($enclosureName,'.')+1);

                $enclosure ='<div class="enclosure"><h1>Fichier média :</h1><a href="'.$enclosure->link.'"> '.$enclosureName.'</a> <span>(Format '.strtoupper($enclosureFormat).', '.Functions::convertFileSize($enclosure->length).')</span></div>';
            }else{
                $enclosure = '';
            }

            $event->setContent($item->get_content().$enclosure);
            $event->setDescription($item->get_description().$enclosure);

            if(trim($event->getDescription())=='')
                $event->setDescription(
                    substr($event->getContent(),0,300)
                    .'…<br><a href="'.$event->getLink()
                    .'">Lire la suite de l\'article</a>'
                );
            if(trim($event->getContent())=='')
                $event->setContent($event->getDescription());

            $event->setCategory($item->get_category());
            $event->save();
            $nbEvents++;
        }

        $listid = "";
        foreach($events as $item){
            $listid.=','.$item;
        }
        $query='UPDATE `'.MYSQL_PREFIX.'event` SET syncId='.$syncId.' WHERE id in (0'.$listid.');';
        $myQuery = $this->customQuery($query);

        $this->lastupdate = $_SERVER['REQUEST_TIME'];
        $this->save();
        return true;
    }


    function removeOldEvents($maxEvent, $currentSyncId){
        if ($maxEvent<=0) return;
        $eventManager = new Event();
        $nbLines = $eventManager->rowCount(array(
            'feed'=>$this->id,
             'unread'=>0,
            'favorite'=>0,
        ));
        $limit = $nbLines - $maxEvent;
        if ($limit<=0) return;
        $tableEvent = '`'.MYSQL_PREFIX."event`";
        $query = "
            DELETE FROM {$tableEvent} WHERE feed={$this->id} AND favorite!=1 AND unread!=1 AND syncId!={$currentSyncId} ORDER BY pubdate ASC LIMIT {$limit}
        ";
        ///@TODO: escape the variables inside mysql
         $this->customExecute($query);
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
        $results = Feed::customQuery("SELECT COUNT(".MYSQL_PREFIX."event.id), ".MYSQL_PREFIX."event.feed FROM ".MYSQL_PREFIX."event WHERE ".MYSQL_PREFIX."event.unread = 1 GROUP BY ".MYSQL_PREFIX."event.feed") ;
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

    function getIsverbose(){
        return $this->isverbose;
    }

    function setIsverbose($isverbose){
        $this->isverbose = $isverbose;
    }

    /** @returns vrai si l'url n'est pas déjà connue .*/
    function notRegistered() {
        return $this->rowCount(array('url' => $this->url)) == 0;
    }

}

?>
