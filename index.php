<?php

/*
 @nom: index
 @auteur: Idleman (idleman@idleman.fr)
 @description:  Page d'accueil et de lecture des flux
*/

require_once('header.php');


Plugin::callHook("index_pre_treatment", array(&$_));

//Récuperation de l'action (affichage) demandée
$action = (isset($_['action'])?$_['action']:'');
$tpl->assign('action',$action);
if($isAlwaysDisplayed) {
    //Récuperation des dossiers de flux par ordre de nom
    $tpl->assign('folders',$folderManager->populate('name'));
    //Recuperation de tous les non Lu
    $tpl->assign('unread',$feedManager->countUnreadEvents());
    //recuperation de tous les flux
    $allFeeds = $feedManager->getFeedsPerFolder();
    $tpl->assign('allFeeds',$allFeeds);
    //recuperation de tous les flux par dossier
    $tpl->assign('allFeedsPerFolder',$allFeeds['folderMap']);
    //recuperation de tous les event nons lu par dossiers
    $tpl->assign('allEvents',$eventManager->getEventCountPerFolder());
    //utilisé pour récupérer le statut d'un feed dans le template (en erreur ou ok)
    $feedState = new Feed();
    $tpl->assign('feedState',$feedState);
}
//afficher ou non le champ OTP
$tpl->assign('otpEnabled', $configurationManager->get('otpEnabled'));

$articleDisplayAuthor = $myUserConfs->articleDisplayAuthor;
$articleDisplayDate = $myUserConfs->articleDisplayDate;
$articleDisplayFolderSort = $myUserConfs->articleDisplayFolderSort;
$articleDisplayHomeSort = $myUserConfs->articleDisplayHomeSort;
$articleDisplayLink = $myUserConfs->articleDisplayLink;
$articleDisplayMode = $myUserConfs->articleDisplayMode;
$articlePerPages = $myUserConfs->articlePerPages;
$displayOnlyUnreadFeedFolder = $myUserConfs->displayOnlyUnreadFeedFolder;
$displayOnlyUnreadFeedFolder_reverse = $myUserConfs->displayOnlyUnreadFeedFolder === 1 ?
    false
    : true;
$optionFeedIsVerbose = $myUserConfs->optionFeedIsVerbose;

$page = 0;
$pages = 0;

$tpl->assign('articleDisplayAuthor',$articleDisplayAuthor);
$tpl->assign('articleDisplayDate',$articleDisplayDate);
$tpl->assign('articleDisplayFolderSort',$articleDisplayFolderSort);
$tpl->assign('articleDisplayHomeSort',$articleDisplayHomeSort);
$tpl->assign('articleDisplayLink',$articleDisplayLink);
$tpl->assign('articleDisplayMode',$articleDisplayMode);
$tpl->assign('articlePerPages',$articlePerPages);
$tpl->assign('displayOnlyUnreadFeedFolder',$displayOnlyUnreadFeedFolder);
$tpl->assign('displayOnlyUnreadFeedFolder_reverse',$displayOnlyUnreadFeedFolder_reverse);

$target = '`'.Event::TABLE_NAME.'`.`title`,`'.Event::TABLE_NAME.'`.`unread`,`'.Event::TABLE_NAME.'`.`favorite`,`'.Event::TABLE_NAME.'`.`feed`,';
if($articleDisplayMode=='summary') $target .= '`'.Event::TABLE_NAME.'`.`description`,';
if($articleDisplayMode=='content') $target .= '`'.Event::TABLE_NAME.'`.`content`,';
if($articleDisplayLink) $target .= '`'.Event::TABLE_NAME.'`.`link`,';
if($articleDisplayDate) $target .= '`'.Event::TABLE_NAME.'`.`pubdate`,';
if($articleDisplayAuthor) $target .= '`'.Event::TABLE_NAME.'`.`creator`,';
$target .= '`'.Event::TABLE_NAME.'`.`id`';

$tpl->assign('target',$target);
$tpl->assign('feeds','');
$tpl->assign('order','');
$tpl->assign('unreadEventsForFolder',0);
$pagesArray = array();

switch($action){
    /* AFFICHAGE DES EVENEMENTS D'UN FLUX EN PARTICULIER */
    case 'selectedFeed':
        $currentFeed = $feedManager->getById($_['feed']);
        $tpl->assign('currentFeed',$currentFeed);
        $numberOfItem = $eventManager->rowCount(array('feed'=>$currentFeed->getId()));
        $allowedOrder = array('date'=>'pubdate DESC','older'=>'pubdate','unread'=>'unread DESC,pubdate DESC');
        $order = (isset($_['order'])?$allowedOrder[$_['order']]:$allowedOrder['unread']);
        $page = (isset($_['page'])?$_['page']:1);
        $pages = ceil($numberOfItem/$articlePerPages);
        $startArticle = ($page-1)*$articlePerPages;
        $events = $currentFeed->getEvents($startArticle,$articlePerPages,$order,$target);

        $tpl->assign('order',(isset($_['order'])?$_['order']:''));

    break;
    /* AFFICHAGE DES EVENEMENTS D'UN DOSSIER EN PARTICULIER */
    case 'selectedFolder':
        $currentFolder = $folderManager->getById($_['folder']);
        $tpl->assign('currentFolder',$currentFolder);
        $numberOfItem = $currentFolder->unreadCount();
        $page = (isset($_['page'])?$_['page']:1);
        $pages = ceil($numberOfItem/$articlePerPages);
        $startArticle = ($page-1)*$articlePerPages;
        if($articleDisplayFolderSort) {$order = '`'.Event::TABLE_NAME.'`.`pubdate` desc';} else {$order = '`'.Event::TABLE_NAME.'`.`pubdate` asc';}
        $events = $currentFolder->getEvents($startArticle,$articlePerPages,$order,$target);


    break;
    /* AFFICHAGE DES EVENEMENTS FAVORIS */
    case 'favorites':
        $numberOfItem = $eventManager->rowCount(array('favorite'=>1));
        $page = (isset($_['page'])?$_['page']:1);
        $pages = ceil($numberOfItem/$articlePerPages);
        $startArticle = ($page-1)*$articlePerPages;
        $events = $eventManager->loadAllOnlyColumn($target,array('favorite'=>1),'pubdate DESC',$startArticle.','.$articlePerPages);
        $tpl->assign('numberOfItem',$numberOfItem);
    break;

    /* AFFICHAGE DES EVENEMENTS NON LUS (COMPORTEMENT PAR DEFAUT) */
    case 'unreadEvents':
    case 'wrongLogin':
        $wrongLogin = true;
    default:
        $wrongLogin = !empty($wrongLogin);
        $tpl->assign('wrongLogin',$wrongLogin);
        if(!$isAlwaysDisplayed) {
            break;
        }
        $filter = array('unread'=>1);
        if($optionFeedIsVerbose) {
            $numberOfItem = $eventManager->rowCount($filter);
        } else {
            $numberOfItem = $eventManager->getEventCountNotVerboseFeed();
        }
        $page = (isset($_['page'])?$_['page']:1);
        $pages = ($articlePerPages>0?ceil($numberOfItem/$articlePerPages):1);
        $startArticle = ($page-1)*$articlePerPages;
        if($articleDisplayHomeSort) {$order = 'pubdate desc';} else {$order = 'pubdate asc';}
        if($optionFeedIsVerbose) {
            $events = $eventManager->loadAllOnlyColumn($target,$filter,$order,$startArticle.','.$articlePerPages);
        } else {
            $events = $eventManager->getEventsNotVerboseFeed($startArticle,$articlePerPages,$order,$target);
        }
        $tpl->assign('numberOfItem',$numberOfItem);

    break;
}
$tpl->assign('pages',$pages);
$tpl->assign('page',$page);

$paginationScale = $myUserConfs->paginationScale;
if (empty($paginationScale)) {
    $configurationManager->put('paginationScale', 5);
    $paginationScale = $myUserConfs->paginationScale;
}

for($i=($page-$paginationScale<=0?1:$page-$paginationScale);$i<($page+$paginationScale>$pages+1?$pages+1:$page+$paginationScale);$i++){
    $pagesArray[]=$i;
}
$tpl->assign('pagesArray',$pagesArray);
$tpl->assign('previousPages',($page-$paginationScale<0?-1:$page-$paginationScale-1));
$tpl->assign('nextPages',($page+$paginationScale>$pages+1?-1:$page+$paginationScale));


Plugin::callHook("index_post_treatment", array(&$events));
$tpl->assign('events',$events);
$tpl->assign('time',$_SERVER['REQUEST_TIME']);
$tpl->assign('hightlighted',0);
$tpl->assign('scroll',false);

$view = 'index';
require_once('footer.php');
?>
