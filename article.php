<?php 

/*
 @nom: article
 @auteur: Maël ILLOUZ (mael.illouz@cobestran.com)
 @description: Page de gestion de l'affichage des articles. Sera utilisé de base ainsi que pour le scroll infini
 */

$scroll = (isset($_['scroll'])?false:true);
if ($scroll) {
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

	$tpl->assign('articleView',$articleView);
	$tpl->assign('articleDisplayLink',$articleDisplayLink);
	$tpl->assign('articleDisplayDate',$articleDisplayDate);
	$tpl->assign('articleDisplayAuthor',$articleDisplayAuthor);
	$tpl->assign('articleDisplayContent',$articleDisplayContent);

	
	$hightlighted = $_['hightlighted'];
	$tpl->assign('hightlighted',$hightlighted);
	
	$tpl->assign('time',$_SERVER['REQUEST_TIME']);

	$prefix=$eventManager->getPrefixTable();
	$target = $prefix.'event.title,'.$prefix.'event.unread,'.$prefix.'event.favorite,'.$prefix.'event.feed,';
	if($articleDisplayContent && $articleView=='partial') $target .= $prefix.'event.description,';
	if($articleDisplayContent && $articleView!='partial') $target .= $prefix.'event.content,';
	if($articleDisplayLink) $target .= $prefix.'event.link,';
	if($articleDisplayDate) $target .= $prefix.'event.pubdate,';
	if($articleDisplayAuthor) $target .= $prefix.'event.creator,';
	$target .= $prefix.'event.id';
	
	$startArticle = $_['scroll']*$articlePerPages;
	$action = $_['action'];
	
	switch($action){
		/* AFFICHAGE DES EVENEMENTS D'UN FLUX EN PARTICULIER */
		case 'selectedFeed':
			$currentFeed = $feedManager->getById($_['feed']);
			$allowedOrder = array('date'=>'pubdate DESC','older'=>'pubdate','unread'=>'unread DESC,pubdate DESC');
			$order = (isset($_['order'])?$allowedOrder[$_['order']]:$allowedOrder['date']);
			$events = $currentFeed->getEvents($startArticle,$articlePerPages,$order,$target);
		break;
		/* AFFICHAGE DES EVENEMENTS D'UN DOSSIER EN PARTICULIER */
		case 'selectedFolder':
			$currentFolder = $folderManager->getById($_['folder']);
			if($articleDisplayFolderSort) {$order = 'pubdate desc';} else {$order = 'pubdate asc';}
			$events = $currentFolder->getEvents($startArticle,$articlePerPages,$order,$target);
		break;
		/* AFFICHAGE DES EVENEMENTS FAVORIS */
		case 'favorites':
			$events = $eventManager->loadAllOnlyColumn($target,array('favorite'=>1),'pubDate DESC',$startArticle.','.$articlePerPages);
		break;
		/* AFFICHAGE DES EVENEMENTS NON LUS (COMPORTEMENT PAR DEFAUT) */
		case 'unreadEvents':
		default:
			if($articleDisplayHomeSort) {$order = 'pubdate desc';} else {$order = 'pubdate asc';}
			$events = $eventManager->loadAllOnlyColumn($target,array('unread'=>1),$order,$startArticle.','.$articlePerPages);
		break;
	}
	$tpl->assign('events',$events);
	$tpl->assign('scroll',$scroll);
	$view = "article";
	$html = $tpl->draw($view);
}
?>
