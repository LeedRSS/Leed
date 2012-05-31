<?php require_once('header.php'); 

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

					?>
					<!-- DOSSIER -->
					<li><h1 class="folder" <?php if(count($feeds)!=0){ ?>onclick="toggleFolder(this,<?php echo $folder->getId(); ?>);"<?php } ?>><?php  echo $folder->getName().' ('.count($feeds).')'; ?> <?php if(count($feeds)!=0){ ?> - <a href="action.php?action=readFolder&folder=<?php  echo $folder->getId() ?>">lire tout</a><?php } ?></h1>
						<!-- FLUX DU DOSSIER -->
						<ul <?php if(!$folder->getIsopen()){ ?>style="display:none;"<?php } ?>>
							<?php if (count($feeds)!=0 ) {
								foreach($feeds as $feed){ ?>
								<li><a href="index.php?action=selectedFeed&feed=<?php echo $feed['id'];?>" alt="<?php echo $feed['url']; ?>" title="<?php echo $feed['url']; ?>"><?php echo $feed['name']; ?> </a><?php if(isset($unread[$feed['id']])){ ?>  <button style="margin-left:10px;" onclick="if(confirm('Tout marquer comme lu pour ce flux?'))window.location='action.php?action=readAll&feed=<?php echo $feed['id']; ?>'"><span alt="marquer comme lu" title="marquer comme lu"><?php echo $unread[$feed['id']]; ?></span></button><?php } ?> </li>
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

				$target = 'title,unread,favorite,';
				if($articleDisplayContent && $articleView=='partial') $target .= 'description,';
				if($articleDisplayContent && $articleView!='partial') $target .= 'content,';
				if($articleDisplayLink) $target .= 'link,';
				if($articleDisplayDate) $target .= 'pubDate,';
				if($articleDisplayAuthor) $target .= 'creator,';
				$target .= 'id';
				

				switch($action){
					/* AFFICHAGE DES EVENEMENTS D'UN FLUX EN PARTICULIER */
					case 'selectedFeed':
						$currentFeed = $feedManager->getById($_['feed']);

						$numberOfItem = $eventManager->rowCount(array('feed'=>$currentFeed->getId()));
						$allowedOrder = array('date'=>'pubdate DESC','unread'=>'unread DESC');
						$order = (isset($_['order'])?$allowedOrder[$_['order']]:$allowedOrder['date']);
						$page = (isset($_['page'])?$_['page']:1);
						$pages = round($numberOfItem/$articlePerPages); 
						$startArticle = ($page-1)*$articlePerPages;
						

						$events = $currentFeed->getEvents($startArticle,$articlePerPages,$order,$target);

						?>
						<h1><a target="_blank" href="<?php echo $currentFeed->getWebSite(); ?>"><?php echo $currentFeed->getName(); ?></a></h1>
						<p><?php echo $currentFeed->getDescription(); ?> - <a href="index.php?action=selectedFeed&feed=<?php echo $_['feed']; ?>&page=<?php echo $page; ?>&order=unread">Voir les non lu en premier<a></p>
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
					<h2><a onclick="readThis(this,<?php echo $event->getId(); ?>);" target="_blank" href="<?php echo $event->getLink(); ?>" alt="<?php echo $plainDescription; ?>" title="<?php echo $plainDescription; ?>"><?php echo $event->getTitle(); ?></a> </h2>
					<!-- DETAILS + OPTIONS -->
					<h3><?php if ($articleDisplayAuthor){ ?>Par <?php echo $event->getCreator(); } if ($articleDisplayDate){ ?> <?php echo $event->getPubdateWithInstant($time); } if ($articleDisplayLink){ ?> - <a href="<?php echo $event->getLink(); ?>" target="_blank">Lien direct vers l'article</a><?php } if($event->getFavorite()!=1){ ?> -  <a class="pointer" onclick="addFavorite(this,<?php echo $event->getId(); ?>);" >Favoriser</a> <?php }else{ ?> <a class="pointer" onclick="removeFavorite(this,<?php echo $event->getId(); ?>);" >D&eacute;favoriser</a> <?php } if($shareOption!=false){ ?> <button  alt="partager sur shaarli" title="partager sur shaarli" onclick="window.location.href='<?php echo $shareOption.'/index.php?post='.rawurlencode($event->getLink()).'&title='.$event->getTitle().'&source=bookmarklet' ?>'">Shaare</button><?php } ?> - <span class="pointer" onclick="readThis(this,<?php echo $event->getId(); ?><?php echo ($action=='unreadEvents' || $action==''?',true':'') ?>);">(lu/non lu)</span></h3>
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
