<?php require_once('header.php'); 

//Récuperation de l'action (affichage) demandée
$action = (isset($_['action'])?$_['action']:'');
//Récuperation des dossiers de flux par ordre de nom
$folders = $folderManager->populate('name');
//Récuperation du chemin vers shaarli si le plugin shaarli est activé
$shareOption = ($configurationManager->get('plugin_shaarli')=='1'?$configurationManager->get('plugin_shaarli_link'):false);  


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
						  	$feeds = $folder->getFeeds();
					?>
					<!-- DOSSIER -->
					<li><h1 class="folder" <?php if(count($feeds)!=0){ ?>onclick="toggleFolder(this,<?php echo $folder->getId(); ?>);"<?php } ?>><?php  echo $folder->getName().' ('.count($feeds).')'; ?> <?php if(count($feeds)!=0){ ?> - <a href="action.php?action=readFolder&folder=<?php  echo $folder->getId() ?>">lire tout</a><?php } ?></h1>
						<!-- FLUX DU DOSSIER -->
						<ul <?php if(!$folder->getIsopen()){ ?>style="display:none;"<?php } ?>>
							<?php if (count($feeds)!=0 ) {foreach($feeds as $feed){ ?>
								<li><a href="index.php?action=selectedFeed&feed=<?php echo $feed->getId();?>" alt="<?php echo $feed->getUrl(); ?>" title="<?php echo $feed->getUrl(); ?>"><?php echo $feed->getName(); ?> </a><?php $unread = $feed->countUnreadEvents(); if($unread!=0){ ?>  <button style="margin-left:10px;" onclick="if(confirm('Tout marquer comme lu pour ce flux?'))window.location='action.php?action=readAll&feed=<?php echo $feed->getId(); ?>'"><span alt="marquer comme lu" title="marquer comme lu"><?php echo $unread; ?></span></button><?php } ?> </li>
							<?php }} ?>
						</ul>
						<!-- FIN FLUX DU DOSSIER -->
					</li>
					<!-- FIN DOSSIER -->
					<?php } ?>
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

				

				switch($action){
					/* AFFICHAGE DES EVENEMENTS D'UN FLUX EN PARTICULIER */
					case 'selectedFeed':
						$currentFeed = $feedManager->getById($_['feed']);
						$numberOfItem = $eventManager->rowCount(array('feed'=>$currentFeed->getId()));
						$page = (isset($_['page'])?$_['page']:1);
						$pages = round($numberOfItem/$articlePerPages); 
						$startArticle = ($page-1)*$articlePerPages;
						$events = $currentFeed->getEvents($startArticle,$articlePerPages,'pubdate DESC');

						?>
						<h1><a target="_blank" href="<?php echo $currentFeed->getWebSite(); ?>"><?php echo $currentFeed->getName(); ?></a></h1>
						<p><?php echo $currentFeed->getDescription(); ?></p>
						<?php

					break;
					/* AFFICHAGE DES EVENEMENTS FAVORIS */
					case 'favorites':
						$numberOfItem = $eventManager->rowCount(array('favorite'=>1));
						$page = (isset($_['page'])?$_['page']:1);
						$pages = round($numberOfItem/$articlePerPages); 
						$startArticle = ($page-1)*$articlePerPages;
						$events = $eventManager->loadAll(array('favorite'=>1),'pubDate DESC',$startArticle.','.$articlePerPages);
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
						$events = $eventManager->loadAll(array('unread'=>1),'pubDate DESC',$startArticle.','.$articlePerPages);
						?>
						<h1>Non lu (<?php echo $numberOfItem; ?>)</h1>
						<?php
					break;
				}
			 ?>
			 	<div class="clear"></div>
				</header>

				<?php foreach($events as $event){ 
					$plainDescription = strip_tags($event->getDescription());
					?>
				<!-- CORPS ARTICLE -->
				<section <?php if(!$event->getUnread()){ ?>class="eventRead"<?php } ?> >
					<!-- TITRE -->
					<h2><a onclick="readThis(this,<?php echo $event->getId(); ?>);" target="_blank" href="<?php echo $event->getGuid(); ?>" alt="<?php echo $plainDescription; ?>" title="<?php echo $plainDescription; ?>"><?php echo $event->getTitle(); ?></a> </h2>
					<!-- DETAILS + OPTIONS -->
					<h3><?php if ($articleDisplayAuthor){ ?>Par <?php echo $event->getCreator(); } if ($articleDisplayLink){ ?> le <?php echo $event->getPubDate(); } if ($articleDisplayLink){ ?>- <a href="<?php echo $event->getGuid(); ?>" target="_blank">Lien direct vers l'article</a><?php } if($event->getFavorite()!=1){ ?> -  <a class="pointer" onclick="addFavorite(this,<?php echo $event->getId(); ?>);" >Favoriser</a> <?php }else{ ?> <a class="pointer" onclick="removeFavorite(this,<?php echo $event->getId(); ?>);" >D&eacute;favoriser</a> <?php } if($shareOption!=false){ ?> <button  alt="partager sur shaarli" title="partager sur shaarli" onclick="window.location.href='<?php echo $shareOption.'/index.php?post='.rawurlencode($event->getGuid()).'&title='.$event->getTitle().'&source=bookmarklet' ?>'">Shaare</button><?php } ?> - <span class="pointer" onclick="readThis(this,<?php echo $event->getId(); ?>);">(marquer comme lu)</span></h3>
					<!-- CONTENU/DESCRIPTION -->
					<?php if($articleDisplayContent){ ?><p><?php if ($articleView=='partial'){echo $event->getDescription();}else{echo $event->getContent();} ?></p> <?php } ?>
				</section>
				<?php } ?>
				<!-- PIED DE PAGE DES ARTICLES -->
				<p>Page <?php echo $page; ?>/<?php echo $pages; ?> : <?php for($i=1;$i<$pages+1;$i++){ ?> <a href="index.php?<?php echo 'action='.$action; if($action=='selectedFeed') echo '&feed='.$currentFeed->getId(); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a> | <?php } ?> </p>
			</article>


		</div> <!-- #main -->

<?php require_once('footer.php'); ?>
