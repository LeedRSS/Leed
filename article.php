<?php

/*
 @nom: article
 @auteur: Maël ILLOUZ (mael.illouz@cobestran.com)
 @description: Page de gestion de l'affichage des articles. Sera utilisé de base ainsi que pour le scroll infini
 */

include ('common.php');
$view = "article";
//recuperation de tous les flux
$allFeeds = $feedManager->getFeedsPerFolder();
$tpl->assign('allFeeds',$allFeeds);
$tpl->assign('scrollpage',$_['scroll']);
// récupération des variables pour l'affichage
$articleDisplayContent = $configurationManager->get('articleDisplayContent');
$articleView = $configurationManager->get('articleView');
$articlePerPages = $configurationManager->get('articlePerPages');
$articleDisplayLink = $configurationManager->get('articleDisplayLink');
$articleDisplayDate = $configurationManager->get('articleDisplayDate');
$articleDisplayAuthor = $configurationManager->get('articleDisplayAuthor');
$articleDisplayHomeSort = $configurationManager->get('articleDisplayHomeSort');
$articleDisplayFolderSort = $configurationManager->get('articleDisplayFolderSort');
$optionFeedIsVerbose = $configurationManager->get('optionFeedIsVerbose');

$tpl->assign('articleView',$articleView);
$tpl->assign('articleDisplayLink',$articleDisplayLink);
$tpl->assign('articleDisplayDate',$articleDisplayDate);
$tpl->assign('articleDisplayAuthor',$articleDisplayAuthor);
$tpl->assign('articleDisplayContent',$articleDisplayContent);


$hightlighted = $_['hightlighted'];
$tpl->assign('hightlighted',$hightlighted);

$tpl->assign('time',$_SERVER['REQUEST_TIME']);

$target = MYSQL_PREFIX.'event.title,'.MYSQL_PREFIX.'event.unread,'.MYSQL_PREFIX.'event.favorite,'.MYSQL_PREFIX.'event.feed,';
if($articleDisplayContent && $articleView=='partial') $target .= MYSQL_PREFIX.'event.description,';
if($articleDisplayContent && $articleView!='partial') $target .= MYSQL_PREFIX.'event.content,';
if($articleDisplayLink) $target .= MYSQL_PREFIX.'event.link,';
if($articleDisplayDate) $target .= MYSQL_PREFIX.'event.pubdate,';
if($articleDisplayAuthor) $target .= MYSQL_PREFIX.'event.creator,';
$target .= MYSQL_PREFIX.'event.id';

$startArticle = ($_['scroll']*$articlePerPages)-$_['nblus'];
if ($startArticle < 0) $startArticle=0;
$action = $_['action'];

switch($action){
    /* AFFICHAGE DES EVENEMENTS D'UN FLUX EN PARTICULIER */
    case 'selectedFeed':
        $currentFeed = $feedManager->getById($_['feed']);
        $allowedOrder = array('date'=>'pubdate DESC','older'=>'pubdate','unread'=>'unread DESC,pubdate DESC');
        $order = (isset($_['order'])?$allowedOrder[$_['order']]:$allowedOrder['date']);
        $events = $currentFeed->getEvents($startArticle,$articlePerPages,$order,$target);
    break;
    /* AFFICHAGE DES EVENEMENTS D'UN FLUX EN PARTICULIER en mode non lus */
    case 'selectedFeedNonLu':
        $currentFeed = $feedManager->getById($_['feed']);
        $filter = array('unread'=>1, 'feed'=>$currentFeed->getId());
        $order = 'pubdate DESC';
        $events = $eventManager->loadAllOnlyColumn($target,$filter,$order,$startArticle.','.$articlePerPages);
    break;
    /* AFFICHAGE DES EVENEMENTS D'UN DOSSIER EN PARTICULIER */
    case 'selectedFolder':
        $currentFolder = $folderManager->getById($_['folder']);
        if($articleDisplayFolderSort) {$order = MYSQL_PREFIX.'event.pubdate desc';} else {$order = MYSQL_PREFIX.'event.pubdate asc';}
        $events = $currentFolder->getEvents($startArticle,$articlePerPages,$order,$target);
    break;
    /* AFFICHAGE DES EVENEMENTS FAVORIS */
    case 'favorites':
        $events = $eventManager->loadAllOnlyColumn($target,array('favorite'=>1),'pubdate DESC',$startArticle.','.$articlePerPages);
    break;
    /* AFFICHAGE DES EVENEMENTS NON LUS (COMPORTEMENT PAR DEFAUT) */
    case 'unreadEvents':
    default:
        $filter = array('unread'=>1);
        if($articleDisplayHomeSort) {$order = 'pubdate desc';} else {$order = 'pubdate asc';}
        if($optionFeedIsVerbose) {
            $events = $eventManager->loadAllOnlyColumn($target,$filter,$order,$startArticle.','.$articlePerPages);
        } else {
            $events = $eventManager->getEventsNotVerboseFeed($startArticle,$articlePerPages,$order,$target);
        }
        break;
}
$tpl->assign('events',$events);
$tpl->assign('scroll',$_['scroll']);
$view = "article";
Plugin::callHook("index_post_treatment", array(&$events));
$html = $tpl->draw($view);

?>
