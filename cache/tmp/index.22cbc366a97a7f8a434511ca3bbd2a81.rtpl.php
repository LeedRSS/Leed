<?php if(!class_exists('raintpl')){exit;}?><!--
 @nom: index
 @auteur: Idleman (idleman@idleman.fr)
 @description: Page d'accueil et de lecture des flux
-->

<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>



<?php if( ($configurationManager->get('articleDisplayAnonymous')=='1') || ($myUser!=false) ){ ?>



		<div id="main" class="wrapper clearfix">
			<!---------->
			<!-- MENU -->
			<!---------->

			<aside>
				<!-- TITRE MENU + OPTION TOUT MARQUER COMME LU -->
				<h3 class="left">Flux</h3> <button style="margin: 20px 10px;" onclick="if(confirm('Tout marquer comme lu pour tous les flux?'))window.location='action.php?action=readAll'">Tout marquer comme lu</button>
				
				<ul class="clear">
					
						
					
						<!--Pour chaques dossier-->
						<?php $counter1=-1; if( isset($folders) && is_array($folders) && sizeof($folders) ) foreach( $folders as $key1 => $value1 ){ $counter1++; ?>

							<?php $feeds=$this->var['feeds']="";?>

							<!--on récupere tous les flux lié au dossier-->
						  	<?php if( isset($allFeedsPerFolder[$value1->getId()]) ){ ?>

						  		<?php $feeds=$this->var['feeds']=$allFeedsPerFolder[$value1->getId()];?>

						  	<?php } ?>

						  	<?php if( isset($allEvents[$value1->getId()]) ){ ?>

						  		<?php $unreadEventsForFolder=$this->var['unreadEventsForFolder']=$allEvents[$value1->getId()];?>

						  	<?php } ?>


				
					<!-- DOSSIER -->
					<li><h1 class="folder"><a alt="Lire les evenements de ce dossier" title="Lire les évenements de ce dossier" href="index.php?action=selectedFolder&folder=<?php echo $value1->getId();?>"><?php echo $value1->getName();?></a> <a class="readFolder" title="Plier/Deplier le dossier" alt="Plier/Deplier le dossier" onclick="toggleFolder(this,<?php echo $value1->getId();?>);" >Deplier</a> <?php if( $unreadEventsForFolder!=0 ){ ?><a class="unreadForFolder" alt="marquer comme lu le(s) <?php echo $unreadEventsForFolder;?> evenement(s) non lu(s) de ce dossier" title="marquer comme lu le(s) <?php echo $unreadEventsForFolder;?> evenement(s) non lu(s) de ce dossier" onclick="if(confirm('Tout marquer comme lu pour ce dossier?'))window.location='action.php?action=readFolder&folder=<?php echo $value1->getId();?>';"><?php echo $unreadEventsForFolder;?> non lu</a><?php } ?></h1>
						<!-- FLUX DU DOSSIER -->
						<ul <?php if( !$value1->getIsopen() ){ ?>style="display:none;"<?php } ?>>
							 
								<?php if( count($feeds)!=0 ){ ?>

									<?php $counter2=-1; if( isset($feeds) && is_array($feeds) && sizeof($feeds) ) foreach( $feeds as $key2 => $value2 ){ $counter2++; ?>

										<li> 
											<div class="feedChip" style="border-color: #<?php echo $value2['color'];?>"></div> <a href="index.php?action=selectedFeed&feed=<?php echo $value2['id'];?>" alt="<?php echo $value2['url'];?>" title="<?php echo $value2['url'];?>"><?php echo $value2['name'];?> </a>

											<?php if( isset($unread[$value2['id']]) ){ ?>  
											<button style="margin-left:10px;" onclick="if(confirm('Tout marquer comme lu pour ce flux?'))window.location='action.php?action=readAll&feed=<?php echo $value2['id'];?>';">
												<span alt="marquer comme lu" title="marquer comme lu"><?php echo $unread[$value2['id']];?></span>
											</button>
											<?php } ?>

										</li>
									<?php } ?>

								<?php } ?>

						</ul>
						<!-- FIN FLUX DU DOSSIER -->
					</li>
					<!-- FIN DOSSIER -->
					<?php $unreadEventsForFolder=$this->var['unreadEventsForFolder']=0;?>

					<?php } ?>


					
					
				</ul>
			</aside>

			<!-------------->
			<!-- ARTICLES -->
			<!-------------->

			<article>
				<!-- ENTETE ARTICLE -->
				<header class="articleHead">
			
				<?php if( $action=='selectedFeed' ){ ?>

				<!-- AFFICHAGE DES EVENEMENTS D'UN FLUX EN PARTICULIER -->
				
						
					<h1 class="articleSection"><a target="_blank" href="<?php echo $currentFeed->getWebSite();?>"><?php echo $currentFeed->getName();?></a></h1>
					<div class="clear"></div>
						<?php echo $currentFeed->getDescription();?>  
							Voir les 
					<a href="index.php?action=selectedFeed&feed=<?php echo $_['feed'];?>&page=<?php echo $page;?>&order=unread">Non lu</a> | 
					<a href="index.php?action=selectedFeed&feed=<?php echo $_['feed'];?>&page=<?php echo $page;?>&order=older">Plus vieux</a> en premier
				<?php } ?>

				
				<?php if( $action=='selectedFolder' ){ ?>

				<!-- AFFICHAGE DES EVENEMENTS D'UN DOSSIER EN PARTICULIER -->		
					<h1 class="articleSection">Dossier : <?php echo $currentFolder->getName();?></h1>
					<p>Tous les evenements non lu pour le dossier <?php echo $currentFolder->getName();?></p>
				<?php } ?>

				
				<?php if( $action=='favorites' ){ ?>

				<!-- AFFICHAGE DES EVENEMENTS FAVORIS -->		
					<h1 class="articleSection">Articles favoris (<?php echo $numberOfItem;?>)</h1>
				<?php } ?>


				
				<?php if( ($action=='unreadEvents') || ($action=='') ){ ?>

				<!-- AFFICHAGE DES EVENEMENTS NON LU (COMPORTEMENT PAR DEFAUT) -->		
					<h1 class="articleSection">Non lu (<?php echo $numberOfItem;?>)</h1>
				<?php } ?>

			
			 	<div class="clear"></div>
				</header>

	
				<?php $counter1=-1; if( isset($events) && is_array($events) && sizeof($events) ) foreach( $events as $key1 => $value1 ){ $counter1++; ?>

					<?php $plainDescription=$this->var['plainDescription']=strip_tags($value1->getDescription());?>

						
					<!-- CORPS ARTICLE -->
					 
					
					<section onclick="targetThisEvent(this);" class="<?php if( $key1==0 ){ ?>eventSelected<?php } ?> <?php if( !$value1->getUnread() ){ ?> eventRead <?php } ?> <?php echo $hightlighted%2==0?'eventHightLighted':'';?>" >
						<a class="anchor" name="<?php echo $value1->getId();?>">
						<a title="Revenir en haut de page" class="goTopButton" href="#pageTop">ˆ</a>
						<!-- TITRE -->
						<h2 class="articleTitle">
							<a onclick="readThis(this,<?php echo $value1->getId();?>{($action=='unreadEvents' || $action==''?',true':',false') ?&gt;,'title');" target="_blank" href="<?php echo $value1->getLink();?>" alt="<?php echo $plainDescription;?>" title="<?php echo $plainDescription;?>"><?php echo $value1->getTitle();?></a> 
						</h2>
						<!-- DETAILS + OPTIONS -->
						<h3 class="articleDetails">
							<?php if( $articleDisplayLink ){ ?>

								<div class="feedChip" style="border-color: #<?php echo $allFeeds['idMap'][$value1->getFeed()]['color'];?>"></div><a href="<?php echo $value1->getLink();?>" target="_blank"><?php echo $allFeeds['idMap'][$value1->getFeed()]['name'];?></a>
							<?php } ?>

							<?php if( $articleDisplayAuthor ){ ?>

							 par <?php echo $value1->getCreator();?>

							<?php } ?>


							<?php if( $articleDisplayDate ){ ?> 
								<?php echo $value1->getPubdateWithInstant($time);?> 
							<?php } ?>

							<?php if( $value1->getFavorite()!=1 ){ ?> -  <a class="pointer favorite" onclick="addFavorite(this,<?php echo $value1->getId();?>);" >Favoriser</a> 
							<?php }else{ ?>

							 <a class="pointer favorite" onclick="removeFavorite(this,<?php echo $value1->getId();?>);" >D&eacute;favoriser</a> 
							 <?php } ?>

							<?php if( $shareOption!=false ){ ?> <button  alt="partager sur shaarli" title="partager sur shaarli" onclick="window.location.href='{$shareOption.'/index.php?post='.rawurlencode($value->getLink()).'&title='.$value->getTitle().'&source=bookmarklet' ?&gt;'">Shaare</button> 
							<?php } ?>

							 <span class="pointer right readUnreadButton" onclick="readThis(this,<?php echo $value1->getId();?><?php echo $action=='unreadEvents' || $action==''?',true':'';?>);">(lu/non lu)</span>
						</h3>

						<!-- CONTENU/DESCRIPTION -->
						<?php if( $articleDisplayContent ){ ?>

						<div class="articleContent">
							<?php if( $articleView=='partial' ){ ?>

								<?php echo $value1->getDescription();?>

							<?php }else{ ?>

								<?php echo $value1->getContent();?>

							<?php } ?>

						</div> 
						<?php } ?>

						

						<?php if( $articleView!='partial' ){ ?>

						<!-- RAPPEL DETAILS + OPTIONS POUR LES ARTICLES AFFICHES EN ENTIER -->
						<h3 class="articleDetails">
							
							<?php if( $shareOption!=false ){ ?> <button  alt="partager sur shaarli" title="partager sur shaarli" onclick="window.location.href='{$shareOption.'/index.php?post='.rawurlencode($value->getLink()).'&title='.$value->getTitle().'&source=bookmarklet' ?&gt;'">Shaare</button> 
							<?php } ?>

							 <span class="pointer right readUnreadButton" onclick="readThis(this,<?php echo $value1->getId();?><?php echo $action=='unreadEvents' || $action==''?',true':'';?>);">(lu/non lu)</span>
							 <?php if( $value1->getFavorite()!=1 ){ ?><a class="right pointer favorite"  onclick="addFavorite(this,<?php echo $value1->getId();?>);" >Favoriser</a> 
							<?php }else{ ?>

							 <a class="right pointer favorite" onclick="removeFavorite(this,<?php echo $value1->getId();?>);" >D&eacute;favoriser</a>  
							 <?php } ?>

							 <div class="clear"></div>
						</h3>
						<?php } ?>




					</section>
					<?php $hightlighted=$this->var['hightlighted']=$hightlighted+1;?>

				<?php } ?>


				<!-- PIED DE PAGE DES ARTICLES -->

				<?php if( $pages!=0 ){ ?>

					<p>Page <?php echo $page;?>/<?php echo $pages;?> : 
					
					
						<?php $counter1=-1; if( isset($pagesArray) && is_array($pagesArray) && sizeof($pagesArray) ) foreach( $pagesArray as $key1 => $value1 ){ $counter1++; ?>

			
							<a href="index.php?action=<?php echo $action;?><?php if( $action=='selectedFeed' ){ ?>&feed=<?php echo $currentFeed->getId();?><?php } ?><?php if( $action=='selectedFolder' ){ ?>&folder=<?php echo $currentFolder->getId();?><?php } ?>&page=<?php echo $value1;?>"><?php echo $value1;?></a> |
						
						<?php } ?>

						





					</p> 
				<?php } ?>



			</article>


		</div> <!-- #main -->

<?php }else{ ?>

	<div id="main" class="wrapper clearfix">
		<article>
				<h3>Vous devez &ecirc;tre connect&eacute; pour consulter vos flux </h3>
				<p>Si vous &ecirc;tes administrateur, vous pouvez r&eacute;gler les droits de visualisation dans la partie administration.</p>
		</article>
	</div>
<?php } ?>


<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>