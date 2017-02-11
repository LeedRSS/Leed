<?php

/*
 @nom: article
 @auteur: Maël ILLOUZ (mael.illouz@cobestran.com)
 @description: Page de gestion de l'affichage des articles. Sera utilisé de base ainsi que pour le scroll infini
 */

include ('common.php');

Plugin::callHook("index_pre_treatment", array(&$_));

$view = "article";
$articleConf = array();
//recuperation de tous les flux
$allFeeds = $feedManager->getFeedsPerFolder();
$tpl->assign('allFeeds',$allFeeds);
$scroll = isset($_['scroll']) ? $_['scroll'] : 0;
$tpl->assign('scrollpage',$scroll);
// récupération des variables pour l'affichage
$articleConf['articlePerPages'] = $myUserConfs->articlePerPages;
$optionFeedIsVerbose = $myUserConfs->optionFeedIsVerbose;

$tpl->assign('articleDisplayAuthor',$myUserConfs->articleDisplayAuthor);
$tpl->assign('articleDisplayDate',$myUserConfs->articleDisplayDate);
$tpl->assign('articleDisplayLink',$myUserConfs->articleDisplayLink);
$tpl->assign('articleDisplayMode',$myUserConfs->articleDisplayMode);

if(isset($_['hightlighted'])) {
    $hightlighted = $_['hightlighted'];
    $tpl->assign('hightlighted',$hightlighted);
}

$tpl->assign('time',$_SERVER['REQUEST_TIME']);

$target = '`'.Event::TABLE_NAME.'`.`title`,`'.Event::TABLE_NAME.'`.`unread`,`'.Event::TABLE_NAME.'`.`favorite`,`'.Event::TABLE_NAME.'`.`feed`,';
if($myUserConfs->articleDisplayMode=='summary') $target .= '`'.Event::TABLE_NAME.'`.`description`,';
if($myUserConfs->articleDisplayMode=='content') $target .= '`'.Event::TABLE_NAME.'`.`content`,';
if($myUserConfs->articleDisplayLink) $target .= '`'.Event::TABLE_NAME.'`.`link`,';
if($myUserConfs->articleDisplayDate) $target .= '`'.Event::TABLE_NAME.'`.`pubdate`,';
if($myUserConfs->articleDisplayAuthor) $target .= '`'.Event::TABLE_NAME.'`.`creator`,';
$target .= '`'.Event::TABLE_NAME.'`.`id`';

$nblus = isset($_['nblus']) ? $_['nblus'] : 0;
$articleConf['startArticle'] = ($scroll*$articleConf['articlePerPages'])-$nblus;
if ($articleConf['startArticle'] < 0) $articleConf['startArticle']=0;
$action = $_['action'];
$tpl->assign('action',$action);

$filter = array();
Plugin::callHook("article_pre_action", array(&$_,&$filter,&$articleConf));
switch($action){
    /* AFFICHAGE DES EVENEMENTS D'UN FLUX EN PARTICULIER */
    case 'selectedFeed':
        $currentFeed = $feedManager->getById($_['feed']);
        $allowedOrder = array('date'=>'pubdate DESC','older'=>'pubdate','unread'=>'unread DESC,pubdate DESC');
        $order = (isset($_['order'])?$allowedOrder[$_['order']]:$allowedOrder['unread']);
        $events = $currentFeed->getEvents($articleConf['startArticle'],$articleConf['articlePerPages'],$order,$target,$filter);
    break;
    /* AFFICHAGE DES EVENEMENTS D'UN DOSSIER EN PARTICULIER */
    case 'selectedFolder':
        $currentFolder = $folderManager->getById($_['folder']);
        if($myUserConfs->articleDisplayFolderSort) {$order = '`'.Event::TABLE_NAME.'`.`pubdate` desc';} else {$order = '`'.Event::TABLE_NAME.'`.`pubdate` asc';}
        $events = $currentFolder->getEvents($articleConf['startArticle'],$articleConf['articlePerPages'],$order,$target,$filter);
    break;
    /* AFFICHAGE DES EVENEMENTS FAVORIS */
    case 'favorites':
        $filter['favorite'] = 1;
        $events = $eventManager->loadAllOnlyColumn($target,$filter,'pubdate DESC',$articleConf['startArticle'].','.$articleConf['articlePerPages']);
    break;
    /* AFFICHAGE DES EVENEMENTS NON LUS (COMPORTEMENT PAR DEFAUT) */
    case 'unreadEvents':
    default:
        $filter['unread'] = 1;
        if($myUserConfs->articleDisplayHomeSort) {$order = 'pubdate desc';} else {$order = 'pubdate asc';}
        if($optionFeedIsVerbose) {
            $events = $eventManager->loadAllOnlyColumn($target,$filter,$order,$articleConf['startArticle'].','.$articleConf['articlePerPages']);
        } else {
            $events = $eventManager->getEventsNotVerboseFeed($articleConf['startArticle'],$articleConf['articlePerPages'],$order,$target);
        }
        break;
}
$tpl->assign('events',$events);
$tpl->assign('scroll',$scroll);
$view = "article";
Plugin::callHook("index_post_treatment", array(&$events));
$html = $tpl->draw($view);

?>
