<?php

/*
 @nom: Feed
 @auteur: Idleman (http://blog.idleman.fr)
 @description: Classe de gestion des flux RSS/ATOM
 */

class Feed extends MysqlEntity{

    protected $id,$name,$url,$events=array(),$description,$website,$folder,$lastupdate,$isverbose,$lastSyncInError;
    protected $TABLE_NAME = 'feed';
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
        'lastSyncInError'=>'boolean',
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

    function getError() { return $this->error; }
    function getLastSyncInError() { return $this->lastSyncInError; }

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
        $isSyncIdOk = is_int($syncId) && $syncId > 0;
        assert($isSyncIdOk);
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
        $feed->set_useragent('Mozilla/4.0 Leed (LightFeed Aggregator) ' . LEED_VERSION_NAME);
        $this->lastSyncInError = 0;
        $this->lastupdate = $_SERVER['REQUEST_TIME'];
        if (!$feed->init()) {
            $this->error = $feed->error;
            $this->lastSyncInError = 1;
            $this->save();
            return false;
        }

        $feed->handle_content_type(); // UTF-8 par défaut pour SimplePie

        if($this->name == '') $this->name = $feed->get_title();
        if($this->name == '') $this->name = $this->url;
        if($this->website == '') $this->website = $feed->get_link();
        if($this->description == '') $this->description = $feed->get_description();

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
            $event->setTitle(html_entity_decode(htmlspecialchars_decode($item->get_title())));
            $event->setPubdate(
                ''==$item->get_date()
                    ? $this->lastupdate
                    : $item->get_date()
            );
            $event->setCreator(
                ''==$item->get_author()
                    ? ''
                    : $item->get_author()->name
            );
            $event->setLink($item->get_permalink());

            $event->setFeed($this->id);
            $event->setUnread(1); // inexistant, donc non-lu
            $enclosure = $this->getEnclosureHtml($item->get_enclosure());
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

        $this->save();
        return true;
    }

    protected function getEnclosureHtml($enclosure) {
        global $i18n;
	$html = '';
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

            $html ='<div class="enclosure"><h1>Fichier média :</h1>';
            $enclosureType = $enclosure->get_type();
            if (strpos($enclosureType, 'image/') === 0) {
                $html .= '<img src="' . $enclosure->link . '" />';
            } elseif (strpos($enclosureType, 'audio/') === 0 || strpos($enclosureType, 'application/ogg') === 0) {
                $html .= '<audio src="' . $enclosure->link . '" preload="none" controls>'._t('BROWSER_AUDIO_ELEMENT_NOT_SUPPORTED').'</audio>';
            } elseif (strpos($enclosureType, 'video/') === 0) {
                $html .= '<video src="' . $enclosure->link . '" preload="none" controls>'._t('BROWSER_VIDEO_ELEMENT_NOT_SUPPORTED').'</video>';
            } else {
                $html .= '<a href="'.$enclosure->link.'"> '.$enclosureName.'</a>';
            }
            $html .= ' <span>(Format '.strtoupper($enclosureFormat).', '.Functions::convertFileSize($enclosure->length).')</span></div>';
        }
        return $html;
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
         $this->customQuery($query);
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


    function getEvents($order,$start=0,$limit=10000,$columns='*',$filter=[]){
        $filter['feed'] = $this->getId();
        $eventManager = new Event();
        $events = $eventManager->loadAllOnlyColumn($columns,$filter,$order,$start.','.$limit);
        return $events;
    }

    function countUnreadEvents(){
        $unreads = array();
        $results = Feed::customQuery("SELECT COUNT(`".MYSQL_PREFIX."event`.`id`), `".MYSQL_PREFIX."event`.`feed` FROM `".MYSQL_PREFIX."event` WHERE `".MYSQL_PREFIX."event`.`unread` = 1 GROUP BY `".MYSQL_PREFIX."event`.`feed`") ;
        if($results!=false){
            $total = 0;
            while($item = $results->fetch_array()){
                $unreads[$item[1]] = $item[0];
                $total += $item[0];
            }
            $unreads['total'] = $total;
        }
        return $unreads;
    }

    function getFeedsPerFolder(){
        $feedsFolderMap = array();
        $feedsIdMap = array();

        $results = Feed::customQuery("SELECT `".MYSQL_PREFIX."feed`.`name` AS name, `".MYSQL_PREFIX."feed`.`id`   AS id, `".MYSQL_PREFIX."feed`.`url`  AS url, `".MYSQL_PREFIX."folder`.`id` AS folder FROM `".MYSQL_PREFIX."feed` LEFT JOIN `".MYSQL_PREFIX."folder` ON ( `".MYSQL_PREFIX."feed`.`folder` = `".MYSQL_PREFIX."folder`.`id` ) ORDER BY `".MYSQL_PREFIX."feed`.`name` ;");
        if($results!=false){
            while($item = $results->fetch_array()){
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
        return $this->lastupdate;
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

    public function synchronize($feeds, $syncTypeStr, $commandLine, $configurationManager, $start) {
        $currentDate = date('d/m/Y H:i:s');
        if (!$commandLine) {
            echo "<p>{$syncTypeStr} {$currentDate}</p>\n";
            echo "<dl>\n";
        } else {
            echo "{$syncTypeStr}\t{$currentDate}\n";
        }
        $maxEvents = $configurationManager->get('feedMaxEvents');
        $nbErrors = 0;
        $nbOk = 0;
        $nbTotal = 0;
        $localTotal = 0; // somme de tous les temps locaux, pour chaque flux
        $nbTotalEvents = 0;
        $syncId = time();
        $enableCache = ($configurationManager->get('synchronisationEnableCache')=='')?0:$configurationManager->get('synchronisationEnableCache');
        $forceFeed = ($configurationManager->get('synchronisationForceFeed')=='')?0:$configurationManager->get('synchronisationForceFeed');

        foreach ($feeds as $feed) {
            $nbEvents = 0;
            $nbTotal++;
            $startLocal = microtime(true);
            $parseOk = $feed->parse($syncId,$nbEvents, $enableCache, $forceFeed);
            $parseTime = microtime(true)-$startLocal;
            $localTotal += $parseTime;
            $parseTimeStr = number_format($parseTime, 3);
            if ($parseOk) { // It's ok
                $errors = array();
                $nbTotalEvents += $nbEvents;
                $nbOk++;
            } else {
                // tableau au cas où il arrive plusieurs erreurs
                $errors = array($feed->getError());

                $nbErrors++;
            }
            $feedName = Functions::truncate($feed->getName(),30);
            $feedUrl = $feed->getUrl();
            $feedUrlTxt = Functions::truncate($feedUrl, 30);
            if ($commandLine) {
                echo date('d/m/Y H:i:s')."\t".$parseTimeStr."\t";
                echo "{$feedName}\t{$feedUrlTxt}\n";
            } else {
                if (!$parseOk)
                    echo '<div class="errorSync">';
                else
                    echo '<div>';
                echo "<dt><i>{$parseTimeStr}s</i> | <a target='_blank' rel='noopener noreferrer' href='{$feedUrl}'>{$feedName}</a></dt>\n";

            }
            foreach($errors as $error) {
                if ($commandLine)
                    echo "$error\n";
                else
                    echo "<dd>$error</dd>\n";
            }
            if (!$parseOk && !$commandLine) echo '</div>';
//             if ($commandLine) echo "\n";
            $feed->removeOldEvents($maxEvents, $syncId);
        }
        $isTotalOk = $nbTotal === $nbOk + $nbErrors;
        assert($isTotalOk);
        $totalTime = microtime(true)-$start;
        $totalTimeStr = number_format($totalTime, 3);
        $currentDate = date('d/m/Y H:i:s');
        if ($commandLine) {
            echo "\t{$nbErrors}\t"._t('ERRORS')."\n";
            echo "\t{$nbOk}\t"._t('GOOD')."\n";
            echo "\t{$nbTotal}\t"._t('AT_TOTAL')."\n";
            echo "\t$currentDate\n";
            echo "\t$nbTotalEvents\n";
            echo "\t{$totalTimeStr}\t"._t('SECONDS')."\n";
        } else {
            echo "</dl>\n";
            echo "<div id='syncSummary'\n";
            echo "<p>"._t('SYNCHRONISATION_COMPLETE')."</p>\n";
            echo "<ul>\n";
            echo "<li>{$nbErrors}\t"._t('ERRORS')."\n";
            echo "<li>{$nbOk}\t"._t('GOOD')."\n";
            echo "<li>{$nbTotal}\t"._t('AT_TOTAL')."\n";
            echo "<li>{$totalTimeStr}\t"._t('SECONDS')."\n";
            echo "<li>{$nbTotalEvents}\t"._t('NEW_ARTICLES')."\n";
            echo "</ul>\n";
            echo "</div>\n";
        }

        if (!$commandLine) {
            echo '</div></body></html>';
        }

    }

}

?>
