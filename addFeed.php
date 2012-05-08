<?php require_once('header.php'); ?>



		<div id="main" class="wrapper clearfix">

			<?php

			
	
			$feeds = $feedManager->populate('name'); 
			$folders = $folderManager->populate('name'); 
			?>

			<aside>
				<h3>Options des flux</h3>
				<ul>
						<li class="pointer" onclick="$('#main section').hide();$('#main #manageBloc').fadeToggle(200);">Gestion des flux</li>
						<li class="pointer" onclick="$('#main section').hide();$('#main #manageFolderBloc').fadeToggle(200);">Gestion des Dossiers</li>
						<li class="pointer" onclick="$('#main section').hide();$('#main #preferenceBloc,#main #preferenceBloc section').fadeToggle(200);">Pr&eacute;f&eacute;rences</li>
						<li class="pointer" onclick="$('#main section').hide();$('#main #addBloc').fadeToggle(200);">Ajout d'un flux</li>
						<li class="pointer" onclick="$('#main section').hide();$('#main #importBloc').fadeToggle(200);">Import</li>
						<li class="pointer" onclick="$('#main section').hide();$('#main #exportBloc').fadeToggle(200);">Export</li>
						<li class="pointer" onclick="$('#main section').hide();$('#main #bookBloc').fadeToggle(200);">Bookmarklet</li>
						
				</ul>
			</aside>
			
			<article>
				
				

				<section id="preferenceBloc">

					<form method="POST" action="action.php?action=updateConfiguration">
					<h2>Pr&eacute;f&eacute;rences :</h2>
					<section>
						<h2>G&eacute;n&eacute;ral</h2>
						<p>Racine du projet : <input type="text" name="root" value="<?php echo str_replace(basename(__FILE__),'','http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>"></p>
						<h3>Laissez bien un "/" en fin de chaine ex : http://monsite.com/leed/</h3>
					</section>

					<section>
					<h2>Pr&eacute;ferences</h2>
					<!--<p>Vue des flux: <input type="radio" value="list" name="view">Liste <input type="radio" value="mosaic" name="view">Mosaique</p>
					<h3>Mosaic : affichage par bloc, style netvives, liste: affichage en liste style rssLounge</h3>-->
					<p>Affichage des articles: <input type="radio" checked="checked" value="partial" name="articleView">Partiel <input type="radio" value="complete" name="articleView">Complet</p>
					<h3>Nb: si vous choissisez un affichage partiel des articles, un click sur ces derniers menera à l'article sur le blog de l'auteur.</h3>
					<p>Nombre d'articles par pages: <input type="text" value="5" name="articlePerPages"></p>
					<p>Affichage du lien direct de l'article: <input type="radio" checked="checked" value="1" name="articleDisplayLink">Oui <input type="radio" value="0" name="articleDisplayLink">Non</p>
					<p>Affichage de la date de l'article: <input type="radio" checked="checked" value="1" name="articleDisplayDate">Oui <input type="radio" value="0" name="articleDisplayDate">Non</p>
					<p>Affichage de l'auteur de l'article: <input type="radio" checked="checked" value="1" name="articleDisplayAuthor">Oui <input type="radio" value="0" name="articleDisplayAuthor">Non</p>
					
					</section>
					<button name="installButton">Enregistrer</button>
					</form>
				</section>


				<section id="manageFolderBloc">
					<h2>Gestion des dossiers (<?php echo count($folders);?>) :</h2>

					<br/>
					<form method="POST" action="action.php?action=addFolder">
						Nouveau dossier <input type="text" name="newFolder"> <button>Ajouter</button>
					</form>
					<br/>
					<table id="feedTable">
						
						<?php foreach($folders as $folder){?>
						<tr><td><?php echo $folder->getName(); ?></td><td><button onclick="renameFolder(this,<?php echo $folder->getId(); ?>)">Renommer</button></td><td><?php if($folder->getId()!='1'){ ?><button onclick="if(confirm('Etes vous sur de vouloir supprimer ce dossier? Cela supprimera tous les flux qu\'il contient.'))window.location='action.php?action=removeFolder&id=<?php echo $folder->getId() ?>'">Supprimer</button><?php } ?></td></tr>
						<?php } ?>
					</table>

				</section>

				<!--<section id="manageBloc">
					<h2>Gestion des flux (<?php echo count($feeds);?>) :</h2>
					<table id="feedTable">
						<?php foreach($feeds as $feed){?>
						<tr><td><?php echo '<a href="'.$feed->getUrl().'">'.$feed->getName().'</a>'; ?></td><td><button onclick="window.location='action.php?action=removeFeed&id=<?php echo $feed->getId() ?>'">Supprimer</button></td></tr>
						<tr><td colspan="2"><p><?php echo $feed->getDescription(); ?></p></td></tr>
						<?php } ?>
					</table>
				</section>-->



			<section id="manageBloc">
					<?php 
						$folders = $folderManager->populate('name');
					?>
					<h2>Gestion des flux :</h2>
					<ul class="clear">
					<?php foreach($folders as $folder){  
						$feeds = $folder->getFeeds();
						?>
					<li><h1 class="folder" onclick="toggleFolder(this,<?php echo $folder->getId(); ?>);"><?php echo $folder->getName().' ('.count($feeds).')'; ?></h1>
						<table  style="width:100%;" <?php if(!$folder->getIsopen()){ ?>style="display:none;"<?php } ?>>
							<?php if (count($feeds)!=0 ) {foreach($feeds as $feed){ ?>
								<tr>
									<td style="width:50%;"><a href="index.php?action=readFeed&feed=<?php echo $feed->getId();?>" alt="<?php echo $feed->getUrl(); ?>" title="<?php echo $feed->getUrl(); ?>"><?php echo $feed->getName(); ?> </a></td>
									<td><select onchange="changeFeedFolder(this,<?php echo $feed->getId();?>);">
										<?php foreach($folders as $listFolder){ ?>
											<option <?php if($feed->getFolder()==$listFolder->getId()){?>selected="selected"<?php } ?> value="<?php echo $listFolder->getId(); ?>"><?php echo $listFolder->getName(); ?></option>
										<?php } ?>
									</select></td>
									<td><button onclick="window.location='action.php?action=removeFeed&id=<?php echo $feed->getId() ?>'">Supprimer</button></td></tr>
							<?php }} ?>
						</table>
					</li>
					<?php } ?>
				</ul>
				</section>


				


				

				
				<section id="bookBloc">
					<h2>Utiliser le bookmarklet :</h2>
					<p>Vous pouvez ajout le bookmaklet ci dessus a votre naviguateur pour vous inscrire plus rapidemment au flux :</p>
					<?php if($myUser!=false){ ?>
					<a class="button" href='javascript:document.location="<?php echo $configurationManager->get('root'); ?>action.php?action=login&newUrl="+escape(document.location)+"&usr=<?php echo sha1($myUser->getPassword().$myUser->getLogin()); ?>"; '>+ Ajouter à Leed</a>
					<?php  }else{  ?>
					<p>Vous devez &eacute;tre connect&eacute; pour voir le bookmarklet.</p>
					<?php } ?>
				</section>
				
				<form action="action.php?action=addFeed" method="POST">
				<section id="addBloc">
					<h2>Ajouter depuis une URL</h2>
					<p>Lien du flux RSS : <input type="text" name="newUrl" placeholder="http://monflux.com/rss"/><button>Ajouter</button></p>
				</section>
				</form>
				<form action="action.php?action=importFeed" method="POST" enctype="multipart/form-data">
				<section id="importBloc">
					<h2>Importer les flux au format opml</h2>
					<p>Fichier OPML : <input name="newImport" type="file"/><button name="importButton">Importer</button></p>
					<p>Nb : L'importation peux prendre un certain temps, laissez votre navigateur tourner et allez vous prendre un caf&eacute; :).</p>
				</section>
				</form>
				<form action="action.php?action=exportFeed" method="POST">
				<section id="exportBloc">
					<h2>Exporter les flux au format opml</h2>
					<p>Fichier OPML : <button name="exportButton">Exporter</button></p>
				</section>
				</form>
			</article>
			
			
		</div> <!-- #main -->

<?php require_once('footer.php'); ?>
