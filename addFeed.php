<?php 

/*
 @nom: addFeed
 @auteur: Idleman (idleman@idleman.fr)
 @description: Page de gestion de toutes les préférences/configurations administrateur
 */

require_once('header.php'); 

if($configurationManager->get('articleDisplayAnonymous')=='1' || $myUser!=false ){
?>



		<div id="main" class="wrapper clearfix">

			<?php
			$feeds = $feedManager->populate('name'); 
			$folders = $folderManager->populate('name'); 
			?>

			<aside>
				<h3>Options des flux</h3>
				<ul>
						<li class="pointer" onclick="$('#main section').hide();$('#main #addBloc').fadeToggle(200);">+ Ajout d'un flux</li>
						<li class="pointer" onclick="$('#main section').hide();$('#main #manageBloc').fadeToggle(200);">Gestion des flux</li>
						<li class="pointer" onclick="$('#main section').hide();$('#main #manageFolderBloc').fadeToggle(200);">Gestion des Dossiers</li>
						<li class="pointer" onclick="$('#main section').hide();$('#main #preferenceBloc,#main #preferenceBloc section').fadeToggle(200);">Pr&eacute;f&eacute;rences</li>
						<li class="pointer" onclick="$('#main section').hide();$('#main #importBloc').fadeToggle(200);">Import</li>
						<li class="pointer" onclick="$('#main section').hide();$('#main #exportBloc').fadeToggle(200);">Export</li>
						<li class="pointer" onclick="$('#main section').hide();$('#main #bookBloc').fadeToggle(200);">Bookmarklet</li>
						<li class="pointer"><a target="_blank" href="./action.php?action=synchronize&format=html">Mise &agrave; jour manuelle des flux</a></li>
				</ul>
			</aside>
			
			<article>
				
				

				<section id="preferenceBloc">
					<form method="POST" action="action.php?action=updateConfiguration">
					<h2>Pr&eacute;f&eacute;rences :</h2>
					<section>
						<h2>G&eacute;n&eacute;ral</h2>
						<p>Racine du projet : <input type="text" name="root" value="<?php echo $configurationManager->get('root'); ?>"></p>
						<h3>Laissez bien un "/" en fin de chaine ex : http://monsite.com/leed/</h3>
					</section>

					<section>
						<h2>Utilisateur</h2>
						<p>Identifiant : <input type="text" name="login" value="<?php echo $myUser->getLogin(); ?>"></p>
						<p>Mot de passe : <input type="text" name="password" value=""></p>
						<h3>Laissez le champs vide si vous ne souhaitez pas changer le mot de passe</h3>
					
					</section>

					<section>
						<h2>Synchronisation</h2>
						<p><input type="radio" <?php $synchronisationType =$configurationManager->get('synchronisationType'); if($synchronisationType=='auto'){ ?> checked="checked" <?php } ?> value="auto" name="synchronisationType"> <strong>Automatique (complet) :</strong> Le script mettra à jour automatiquement tous vos flux en une seule fois, ceci permet la mise &agrave; jour en une foix de tous vos flux mais peux faire ramer votre serveur, les appels cron ne doivent pas être trop rapproch&eacute;s</p>
						<p><input type="radio" <?php if($synchronisationType=='graduate'){ ?> checked="checked" <?php } ?>  value="graduate" name="synchronisationType"> <strong>Automatique (gradu&eacute;) :</strong> Le script mettra à jour automatiquement les 10 flux les plus vieux en terme de mise &agrave; jour, ceci permet d'alleger la charge serveur et d'eviter els timeout intempestif mais necessiteun appel de cron plus fréquent afin de mettre à jour le plus de flux possible</p>
						<p><input type="radio" <?php if($synchronisationType=='manual'){ ?> checked="checked" <?php } ?>  value="manual" name="synchronisationType"> <strong>Automatique (complet) :</strong> Le script ne fait aucune mise à jour automatique, vous devez faire vous même les mises &agrave; jour depuis l'espace administration.</p>
					</section>

					<section>
						<h2>Pr&eacute;ferences</h2>
						<p>Autoriser la lecture anonyme: <input type="radio"  <?php $articleDisplayAnonymous = $configurationManager->get('articleDisplayAnonymous');  if($articleDisplayAnonymous=='1'){ ?> checked="checked" <?php } ?> value="1" name="articleDisplayAnonymous">Oui <input type="radio" <?php if($articleDisplayAnonymous=='0'){ ?> checked="checked" <?php } ?> value="0" name="articleDisplayAnonymous">Non</p>
						<h3>Nb: si vous choisissez cette option, les utilisateurs non authentifi&eacute; pourront consulter vos flux (sans pouvoir les marquer comme lu/non lu)</h3>
						<p>Nombre d'articles par pages: <input type="text" value="<?php echo $configurationManager->get('articlePerPages'); ?>" name="articlePerPages"></p>
						<p>Affichage du lien direct de l'article: <input type="radio" <?php $articleDisplayLink = $configurationManager->get('articleDisplayLink'); if($articleDisplayLink=='1'){ ?> checked="checked" <?php } ?>value="1" name="articleDisplayLink">Oui <input type="radio" <?php if($articleDisplayLink=='0'){ ?> checked="checked" <?php } ?> value="0" name="articleDisplayLink">Non</p>
						<p>Affichage de la date de l'article: <input type="radio" <?php $articleDisplayDate = $configurationManager->get('articleDisplayDate'); if($articleDisplayDate=='1'){ ?> checked="checked" <?php } ?> value="1" name="articleDisplayDate">Oui <input type="radio" <?php if($articleDisplayDate=='0'){ ?> checked="checked" <?php } ?> value="0" name="articleDisplayDate">Non</p>
						<p>Affichage de l'auteur de l'article: <input type="radio" <?php $articleDisplayAuthor = $configurationManager->get('articleDisplayAuthor'); if($articleDisplayAuthor=='1'){ ?> checked="checked" <?php } ?> value="1" name="articleDisplayAuthor">Oui <input type="radio" <?php if($articleDisplayAuthor=='0'){ ?> checked="checked" <?php } ?> value="0" name="articleDisplayAuthor">Non</p>
						<p>Affichage du contenu de l'article: <input type="radio"  <?php $articleDisplayContent = $configurationManager->get('articleDisplayContent'); if($articleDisplayContent=='1'){ ?> checked="checked" <?php } ?> value="1" name="articleDisplayContent">Oui <input type="radio" <?php if($articleDisplayContent=='0'){ ?> checked="checked" <?php } ?> value="0" name="articleDisplayContent">Non</p>
						<p>Type d'affichage du contenu: <input type="radio" <?php $articleView = $configurationManager->get('articleView'); if($articleView=='partial'){ ?> checked="checked" <?php } ?> value="partial" name="articleView">Partiel <input type="radio" <?php if($articleView=='complete'){ ?> checked="checked" <?php } ?> value="complete" name="articleView">Complet</p>
						<h3>Nb: si vous choissisez un affichage partiel des articles, un click sur ces derniers menera à l'article sur le blog de l'auteur.</h3>
						<p>Conserver les <input type="text" value="<?php echo $configurationManager->get('feedMaxEvents'); ?>" name="feedMaxEvents"> derniers &eacute;venement d'un flux</p>
						<h3>Nb: Plus il y aura d'&eacute;venements &agrave; conserver, plus votre abse de données sera importante. Nous vous conseillons de garder les 50 derniers evenements maximums pour conserver une performance correcte.<br>Notez que vos &eacute;venements marqu&eacute;s comme favoris ne seront jamais supprim&eacute;s</h3>
					
					</section>

					<section>
						<h2>Options</h2>
						<p><input onchange="$('.shaarliBlock').slideToggle(200);" <?php if($configurationManager->get('plugin_shaarli')=='1'){ ?> checked="checked" <?php } ?> type="checkbox" name="plugin_shaarli"> Activer le partage direct avec <a target="_blank" href="http://sebsauvage.net/wiki/doku.php?id=php:shaarli">shaarli</a></p>
						<p class="shaarliBlock" <?php if($configurationManager->get('plugin_shaarli')!='1') { ?>style="display:none;"<?php } ?>>Lien vers votre shaarli: <input style="width:100%;" type="text" placeholder="http://mon.domaine.com/shaarli/" value="<?php echo $configurationManager->get('plugin_shaarli_link'); ?>" name="plugin_shaarli_link"></p>
						<h3>Nb: cette option affichera un bouton a coté de chaque news pour vous proposer de la partager/stocker sur le gestionnaire de lien shaarli.</h3>
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





			<section id="manageBloc">
					<?php 
						$folders = $folderManager->populate('name');
					?>
					<h2>Gestion des flux :</h2>
					<ul class="clear">
					<?php foreach($folders as $folder){  
						$feeds = $folder->getFeeds();
						?>
					<li><h1 class="folder" ><?php echo $folder->getName().' ('.count($feeds).')'; ?></h1>
						<table  style="width:100%;">
							<?php if (count($feeds)!=0 ) {foreach($feeds as $feed){ ?>
								<tr>



									<td style="width:50%;"><a href="index.php?action=selectedFeed&feed=<?php echo $feed->getId();?>"><?php echo $feed->getName(); ?></a></td>
									<td><select onchange="changeFeedFolder(this,<?php echo $feed->getId();?>);">
										<?php foreach($folders as $listFolder){ ?>
											<option <?php if($feed->getFolder()==$listFolder->getId()){?>selected="selected"<?php } ?> value="<?php echo $listFolder->getId(); ?>"><?php echo $listFolder->getName(); ?></option>
										<?php } ?>
									</select></td>
									<td><button onclick="renameFeed(this,<?php echo $feed->getId(); ?>)">Renommer</button></td>
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
					<p>Lien du flux RSS : <input type="text" name="newUrl" placeholder="http://monflux.com/rss"/>&nbsp;
					 <select name="newUrlCategory">
						<?php foreach($folders as $folder){ ?>
							<option <?php if($folder->getId()==1){ ?>selected="selected"<?php } ?> value="<?php echo $folder->getId(); ?>"><?php echo $folder->getName(); ?></option>
						<?php } ?>
					</select>
					 <button>Ajouter</button></p>
				
				</section>
				</form>
				
				<section id="importBloc">
					 <iframe class="importFrame" src="action.php?action=importForm" name="idFrame" id="idFrame" width="100%" height="300" ></iframe>
				</section>
				
				<form action="action.php?action=exportFeed" method="POST">
				<section id="exportBloc">
					<h2>Exporter les flux au format opml</h2>
					<p>Fichier OPML : <button name="exportButton">Exporter</button></p>
				</section>
				</form>
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
