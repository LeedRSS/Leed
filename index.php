<?php 

/*
 @nom: index
 @auteur: Idleman (idleman@idleman.fr)
 @description:  Page d'accueil et de lecture des flux
 */

require_once('header.php'); 

if($configurationManager->get('articleDisplayAnonymous')=='1' || $myUser!=false ){

//Récuperation de l'action (affichage) demandée
$action = (isset($_['action'])?$_['action']:'');
//Récuperation des dossiers de flux par ordre de nom
$folders = $folderManager->populate('name');
//Récuperation du chemin vers shaarli si le plugin shaarli est activé
$shareOption = ($configurationManager->get('plugin_shaarli')=='1'?$configurationManager->get('plugin_shaarli_link'):false);  
//Recuperation de tous les non Lu
$unread = $feedManager->countUnreadEvents();
//recuperation de tous les flux
$allFeeds = $feedManager->getFeedsPerFolder();
//recuperation de tous les event nons lu par dossiers
$allEvents = $eventManager->getEventCountPerFolder();

?>
		<div id="main" class="wrapper clearfix">
			<!--//////-->
			<!-- MENU -->
			<!--//////-->

			<aside>
				<!-- TITRE MENU + OPTION TOUT MARQUER COMME LU -->
				<h3 class="left">Flux</h3> <button style="margin: 20px 10px;" onclick="if(confirm('Tout marquer comme lu pour tous les flux?'))window.location='action.php?action=readAll'">Tout marquer comme lu</button>
				
				<ul class="clear">
					<?php 
						//Pour chaques dossier
						foreach($folders as $folder){  
							//on récupere tous les flux lié au dossier
						  	//$feeds = $folder->getFeeds();
						  	$feeds = (isset($allFeeds[$folder->getId()])?$allFeeds[$folder->getId()]:array());
						  	$unreadEventsForFolder = (isset($allEvents[$folder->getId()])?$allEvents[$folder->getId()]:0);
					?>
					<!-- DOSSIER -->
					<li><h1 class="folder"><a alt="Lire les evenements de ce dossier" title="Lire les évenements de ce dossier" href="index.php?action=selectedFolder&folder=<?php echo $folder->getId(); ?>"><?php  echo $folder->getName();?></a> <a class="readFolder" title="Plier/Deplier le dossier" alt="Plier/Deplier le dossier" onclick="toggleFolder(this,<?php echo $folder->getId(); ?>);" >Deplier</a> <?php if($unreadEventsForFolder!=0){ ?><a class="unreadForFolder" alt="marquer comme lu le(s) <?php echo $unreadEventsForFolder; ?> evenement(s) non lu(s) de ce dossier" title="marquer comme lu le(s) <?php echo $unreadEventsForFolder; ?> evenement(s) non lu(s) de ce dossier" onclick="if(confirm('Tout marquer comme lu pour ce dossier?'))window.location='action.php?action=readFolder&folder=<?php  echo $folder->getId(); ?>';"><?php echo $unreadEventsForFolder.' non lu</a>'; } ?></h1>
						<!-- FLUX DU DOSSIER -->
						<ul <?php if(!$folder->getIsopen()){ ?>style="display:none;"<?php } ?>>
							<?php if (count($feeds)!=0 ) {
								foreach($feeds as $feed){ ?>
								<li><a href="index.php?action=selectedFeed&feed=<?php echo $feed['id'];?>" alt="<?php echo $feed['url']; ?>" title="<?php echo $feed['url']; ?>"><?php echo $feed['name']; ?> </a><?php if(isset($unread[$feed['id']])){ ?>  <button style="margin-left:10px;" onclick="if(confirm('Tout marquer comme lu pour ce flux?'))window.location='action.php?action=readAll&feed=<?php echo $feed['id']; ?>';"><span alt="marquer comme lu" title="marquer comme lu"><?php echo $unread[$feed['id']]; ?></span></button><?php } ?> </li>
							<?php }} ?>
						</ul>
						<!-- FIN FLUX DU DOSSIER -->
					</li>
					<!-- FIN DOSSIER -->
					<?php }

					unset($unread);
					unset($allFeeds);
					unset($folders);
					 ?>
				</ul>
			</aside>

			<!--///////////-->
			<!-- ARTICLES -->
			<!--///////////-->

			<article>
				<!-- ENTETE ARTICLE -->
				<header>
			<?php 
				$articleDisplayContent = $configurationManager->get('articleDisplayContent');
				$articleView = $configurationManager->get('articleView');
				$articlePerPages = $configurationManager->get('articlePerPages');
				$articleDisplayLink = $configurationManager->get('articleDisplayLink');
				$articleDisplayDate = $configurationManager->get('articleDisplayDate');
				$articleDisplayAuthor = $configurationManager->get('articleDisplayAuthor');

				$target = 'event.title,event.unread,event.favorite,';
				if($articleDisplayContent && $articleView=='partial') $target .= 'event.description,';
				if($articleDisplayContent && $articleView!='partial') $target .= 'event.content,';
				if($articleDisplayLink) $target .= 'event.link,';
				if($articleDisplayDate) $target .= 'event.pubDate,';
				if($articleDisplayAuthor) $target .= 'event.creator,';
				$target .= 'event.id';
				

				switch($action){
					/* AFFICHAGE DES EVENEMENTS D'UN FLUX EN PARTICULIER */
					case 'selectedFeed':
						$currentFeed = $feedManager->getById($_['feed']);

						$numberOfItem = $eventManager->rowCount(array('feed'=>$currentFeed->getId()));
						$allowedOrder = array('date'=>'pubdate DESC','older'=>'pubdate','unread'=>'unread DESC,pubdate DESC');
						$order = (isset($_['order'])?$allowedOrder[$_['order']]:$allowedOrder['date']);
						$page = (isset($_['page'])?$_['page']:1);
						$pages = round($numberOfItem/$articlePerPages); 
						$startArticle = ($page-1)*$articlePerPages;
						

						$events = $currentFeed->getEvents($startArticle,$articlePerPages,$order,$target);

						?>
						<h1><a target="_blank" href="<?php echo $currentFeed->getWebSite(); ?>"><?php echo $currentFeed->getName(); ?></a></h1>
						<p><?php echo $currentFeed->getDescription(); ?> <br/> - 
							Voir les 
						   <a href="index.php?action=selectedFeed&feed=<?php echo $_['feed']; ?>&page=<?php echo $page; ?>&order=unread">Non lu</a>
						 | <a href="index.php?action=selectedFeed&feed=<?php echo $_['feed']; ?>&page=<?php echo $page; ?>&order=older">Plus vieux</a>
						  en premier</p>
						<?php

					break;
					/* AFFICHAGE DES EVENEMENTS D'UN DOSSIER EN PARTICULIER */
					case 'selectedFolder':
						$currentFolder = $folderManager->getById($_['folder']);
						$numberOfItem = $currentFolder->unreadCount();
						$page = (isset($_['page'])?$_['page']:1);
						$pages = round($numberOfItem/$articlePerPages); 
						$startArticle = ($page-1)*$articlePerPages;
						

						$events = $currentFolder->getEvents($startArticle,$articlePerPages,'event.pubdate DESC',$target);

						?>
						<h1>Dossier : <?php echo $currentFolder->getName(); ?></h1>
						<p>Tous les evenements non lu pour le dossier <?php echo $currentFolder->getName(); ?></p>
						<?php

					break;
					/* AFFICHAGE DES EVENEMENTS FAVORIS */
					case 'favorites':
						$numberOfItem = $eventManager->rowCount(array('favorite'=>1));
						$page = (isset($_['page'])?$_['page']:1);
						$pages = round($numberOfItem/$articlePerPages); 
						$startArticle = ($page-1)*$articlePerPages;


						$events = $eventManager->loadAllOnlyColumn($target,array('favorite'=>1),'pubDate DESC',$startArticle.','.$articlePerPages);
						?>
						<h1>Articles favoris (<?php echo $numberOfItem; ?>)</h1>
						<?php
					break;

					/* AFFICHAGE DES EVENEMENTS NON LU (COMPORTEMENT PAR DEFAUT) */
					case 'unreadEvents':
					default:
						$numberOfItem = $eventManager->rowCount(array('unread'=>1));
						$page = (isset($_['page'])?$_['page']:1);
						$pages = round($numberOfItem/$articlePerPages); 
						$startArticle = ($page-1)*$articlePerPages;
						$events = $eventManager->loadAllOnlyColumn($target,array('unread'=>1),'pubDate DESC',$startArticle.','.$articlePerPages);
						?>
						<h1>Non lu (<?php echo $numberOfItem; ?>)</h1>
						<?php
					break;
				}
			 ?>
			 	<div class="clear"></div>
				</header>

				<?php 
					$time = $_SERVER['REQUEST_TIME'];
					foreach($events as $event){ 
					$plainDescription = strip_tags($event->getDescription());
					?>
				<!-- CORPS ARTICLE -->
				<section <?php if(!$event->getUnread()){ ?>class="eventRead"<?php } ?> >
					<!-- TITRE -->
					<h2><a onclick="readThis(this,<?php echo $event->getId(); ?><?php echo ($action=='unreadEvents' || $action==''?',true':'') ?>);" target="_blank" href="<?php echo $event->getLink(); ?>" alt="<?php echo $plainDescription; ?>" title="<?php echo $plainDescription; ?>"><?php echo $event->getTitle(); ?></a> </h2>
					<!-- DETAILS + OPTIONS -->
					<h3><?php if ($articleDisplayAuthor){ ?>Par <?php echo $event->getCreator(); } if ($articleDisplayDate){ ?> <?php echo $event->getPubdateWithInstant($time); } if ($articleDisplayLink){ ?> - <a href="<?php echo $event->getLink(); ?>" target="_blank">Lien direct vers l'article</a><?php } if($event->getFavorite()!=1){ ?> -  <a class="pointer" onclick="addFavorite(this,<?php echo $event->getId(); ?>);" >Favoriser</a> <?php }else{ ?> <a class="pointer" onclick="removeFavorite(this,<?php echo $event->getId(); ?>);" >D&eacute;favoriser</a> <?php } if($shareOption!=false){ ?> <button  alt="partager sur shaarli" title="partager sur shaarli" onclick="window.location.href='<?php echo $shareOption.'/index.php?post='.rawurlencode($event->getLink()).'&title='.$event->getTitle().'&source=bookmarklet' ?>'">Shaare</button><?php } ?> - <span class="pointer right" onclick="readThis(this,<?php echo $event->getId(); ?><?php echo ($action=='unreadEvents' || $action==''?',true':'') ?>);">(lu/non lu)</span></h3>
					<!-- CONTENU/DESCRIPTION -->
					<?php if($articleDisplayContent){ ?><p><?php if ($articleView=='partial'){echo $event->getDescription();}else{echo $event->getContent();} ?></p> <?php } ?>
				</section>
				<?php } ?>
				<!-- PIED DE PAGE DES ARTICLES -->
				<?php if($pages!=0) { ?><p>Page <?php echo $page; ?>/<?php echo $pages; ?> : <?php for($i=1;$i<$pages+1;$i++){ ?> <a href="index.php?<?php echo 'action='.$action; if($action=='selectedFeed') echo '&feed='.$currentFeed->getId(); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a> | <?php } ?> </p> <?php } ?>
			</article>


		</div> <!-- #main -->

<?php 

}else{
	?>
	<div id="main" class="wrapper clearfix">
		<article>
				<h3>Vous devez &ecirc;tre connect&eacute; pour consulter vos flux </h3>
				<p>Si vous &ecirc;tes administrateur, vous pouvez r&eacute;gler les droits de visualisation dans la partie administration.</p>
		</article>
	</div>

	<?php 
}

require_once('footer.php'); ?>
