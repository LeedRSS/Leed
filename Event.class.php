<?php

/*
 @nom: Event
 @auteur: Idleman (http://blog.idleman.fr)
 @description: Classe de gestion des évenements/news liés a chaques flux RSS/ATOM
 */

class Event extends MysqlEntity{

    protected $id,$title,$guid,$content,$description,$pudate,$link,$feed,$category,$creator,$unread,$favorite;
    protected $TABLE_NAME = 'event';
    protected $object_fields =
    array(
        'id'=>'key',
        'guid'=>'longstring',
        'title'=>'string',
        'creator'=>'string',
        'content'=>'extralongstring',
        'description'=>'longstring',
        'link'=>'longstring',
        'unread'=>'integer',
        'feed'=>'integer',
        'unread'=>'integer',
        'favorite'=>'integer',
        'pubdate'=>'integer',
        'syncId'=>'integer',
    );

    protected $object_fields_index =
    array(
        'feed'=>'index',
        'unread'=>'index',
        'favorite'=>'index'
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
        $results = $this->customQuery('SELECT COUNT(`'.MYSQL_PREFIX.$this->TABLE_NAME.'`.`id`),`'.MYSQL_PREFIX.'feed`.`folder` FROM `'.MYSQL_PREFIX.$this->TABLE_NAME.'` LEFT JOIN `'.MYSQL_PREFIX.'feed` ON (`'.MYSQL_PREFIX.'event`.`feed` = `'.MYSQL_PREFIX.'feed`.`id`) WHERE `'.MYSQL_PREFIX.$this->TABLE_NAME.'`.`unread`=1 GROUP BY `'.MYSQL_PREFIX.'feed`.`folder`');
        while($item = $results->fetch_array()){
            $events[$item[1]] = intval($item[0]);
        }

        return $events;
    }

    function getEventCountNotVerboseFeed(){
        $results = $this->customQuery('SELECT COUNT(1) FROM `'.MYSQL_PREFIX.$this->TABLE_NAME.'` LEFT JOIN `'.MYSQL_PREFIX.'feed` ON (`'.MYSQL_PREFIX.'event`.`feed` = `'.MYSQL_PREFIX.'feed`.`id`) WHERE `'.MYSQL_PREFIX.$this->TABLE_NAME.'`.`unread`=1 AND `'.MYSQL_PREFIX.'feed`.`isverbose`=0');
        while($item = $results->fetch_array()){
            $nbitem =  $item[0];
        }

        return $nbitem;
    }

    function getEventsNotVerboseFeed($order,$start=0,$limit=10000,$columns='*'){
        $eventManager = new Event();
        $objects = array();
        $results = $this->customQuery('SELECT '.$columns.' FROM `'.MYSQL_PREFIX.'event` LEFT JOIN `'.MYSQL_PREFIX.'feed` ON (`'.MYSQL_PREFIX.'event`.`feed` = `'.MYSQL_PREFIX.'feed`.`id`) WHERE `'.MYSQL_PREFIX.'event`.`unread`=1 AND `'.MYSQL_PREFIX.'feed`.`isverbose` = 0 ORDER BY '.$order.' LIMIT '.$start.','.$limit);
        if($results!=false){
            while($item = $results->fetch_array()){
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
        if (empty($this->pubdate)) return '';
        $alpha = $instant - $this->pubdate;
        if ($alpha < 86400 ){
            $hour = floor($alpha/3600);
            $alpha = ($hour!=0?$alpha-($hour*3600):$alpha);
            $minuts = floor($alpha/60);
            if ($hour!=0) {
                return _t('PUBDATE_WITHINSTANT_LOWERH24',array($hour,$minuts));
            } else {
                return _t('PUBDATE_WITHINSTANT_LOWERH1',array($minuts));
            }
        }else{
            $date=$this->getPubdate(_t('FORMAT_DATE_HOURS'));
            return _t('PUBDATE_WITHINSTANT',array($date));
        }
    }

    function setPubdate($pubdate){
        $this->pubdate = (is_numeric($pubdate)?$pubdate:strtotime($pubdate));
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
        $this->title = mb_substr($title, 0, self::LEN_STRING);
    }

    function getContent(){
        return $this->content;
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

    function getSyncId(){
        return $this->syncId;
    }

    function setSyncId($syncId){
        $this->syncId = $syncId;
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
