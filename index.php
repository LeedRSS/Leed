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


$articleDisplayContent = $configurationManager->get('articleDisplayContent');
$articleView = $configurationManager->get('articleView');
$articlePerPages = $configurationManager->get('articlePerPages');
$articleDisplayLink = $configurationManager->get('articleDisplayLink');
$articleDisplayDate = $configurationManager->get('articleDisplayDate');
$articleDisplayAuthor = $configurationManager->get('articleDisplayAuthor');
$articleDisplayHomeSort = $configurationManager->get('articleDisplayHomeSort');
$articleDisplayFolderSort = $configurationManager->get('articleDisplayFolderSort');

$tpl->assign('articleDisplayContent',$configurationManager->get('articleDisplayContent'));
$tpl->assign('articleView',$configurationManager->get('articleView'));
$tpl->assign('articlePerPages',$configurationManager->get('articlePerPages'));
$tpl->assign('articleDisplayLink',$configurationManager->get('articleDisplayLink'));
$tpl->assign('articleDisplayDate',$configurationManager->get('articleDisplayDate'));
$tpl->assign('articleDisplayAuthor',$configurationManager->get('articleDisplayAuthor'));
$tpl->assign('articleDisplayHomeSort',$configurationManager->get('articleDisplayHomeSort'));
$tpl->assign('articleDisplayFolderSort',$configurationManager->get('articleDisplayFolderSort'));

$target = MYSQL_PREFIX.'event.title,'.MYSQL_PREFIX.'event.unread,'.MYSQL_PREFIX.'event.favorite,'.MYSQL_PREFIX.'event.feed,';
if($articleDisplayContent && $articleView=='partial') $target .= MYSQL_PREFIX.'event.description,';
if($articleDisplayContent && $articleView!='partial') $target .= MYSQL_PREFIX.'event.content,';
if($articleDisplayLink) $target .= MYSQL_PREFIX.'event.link,';
if($articleDisplayDate) $target .= MYSQL_PREFIX.'event.pubdate,';
if($articleDisplayAuthor) $target .= MYSQL_PREFIX.'event.creator,';
$target .= MYSQL_PREFIX.'event.id';

$tpl->assign('target',$target);
$tpl->assign('feeds','');
$tpl->assign('order','');
$tpl->assign('unreadEventsForFolder','');
$pagesArray = array();				

				switch($action){
					/* AFFICHAGE DES EVENEMENTS D'UN FLUX EN PARTICULIER */
					case 'selectedFeed':
						$currentFeed = $feedManager->getById($_['feed']);
						$tpl->assign('currentFeed',$currentFeed);
						$numberOfItem = $eventManager->rowCount(array('feed'=>$currentFeed->getId()));
						$allowedOrder = array('date'=>'pubdate DESC','older'=>'pubdate','unread'=>'unread DESC,pubdate DESC');
						$order = (isset($_['order'])?$allowedOrder[$_['order']]:$allowedOrder['date']);
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
						if($articleDisplayFolderSort) {$order = MYSQL_PREFIX.'event.pubdate desc';} else {$order = MYSQL_PREFIX.'event.pubdate asc';}
						$events = $currentFolder->getEvents($startArticle,$articlePerPages,$order,$target);


					break;
					/* AFFICHAGE DES EVENEMENTS FAVORIS */
					case 'favorites':
						$numberOfItem = $eventManager->rowCount(array('favorite'=>1));
						$page = (isset($_['page'])?$_['page']:1);
						$pages = ceil($numberOfItem/$articlePerPages); 
						$startArticle = ($page-1)*$articlePerPages;
						$events = $eventManager->loadAllOnlyColumn($target,array('favorite'=>1),'pubDate DESC',$startArticle.','.$articlePerPages);
						$tpl->assign('numberOfItem',$numberOfItem);


					break;

					/* AFFICHAGE DES EVENEMENTS NON LUS (COMPORTEMENT PAR DEFAUT) */
					case 'unreadEvents':
					default:
						$numberOfItem = $eventManager->rowCount(array('unread'=>1));
						$page = (isset($_['page'])?$_['page']:1);
						$pages = ceil($numberOfItem/$articlePerPages); 
						$startArticle = ($page-1)*$articlePerPages;
						if($articleDisplayHomeSort) {$order = 'pubdate desc';} else {$order = 'pubdate asc';}
						$events = $eventManager->loadAllOnlyColumn($target,array('unread'=>1),$order,$startArticle.','.$articlePerPages);
						$tpl->assign('numberOfItem',$numberOfItem);

					break;
				}
				$tpl->assign('pages',$pages);
				$tpl->assign('page',$page);

				for($i=($page-PAGINATION_SCALE<=0?1:$page-PAGINATION_SCALE);$i<($page+PAGINATION_SCALE>$pages+1?$pages+1:$page+PAGINATION_SCALE);$i++){
					$pagesArray[]=$i;
				}
				$tpl->assign('pagesArray',$pagesArray);
				$tpl->assign('previousPages',($page-PAGINATION_SCALE<0?-1:$page-PAGINATION_SCALE-1));
				$tpl->assign('nextPages',($page+PAGINATION_SCALE>$pages+1?-1:$page+PAGINATION_SCALE));


				Plugin::callHook("index_post_treatment", array(&$events));
				$tpl->assign('events',$events);
				$tpl->assign('time',$_SERVER['REQUEST_TIME']);
				$tpl->assign('hightlighted',0);

$view = 'index';
require_once('footer.php'); 
?>
