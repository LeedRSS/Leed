<?php 

/*
 @nom: about
 @auteur: Idleman (idleman@idleman.fr)
 @description: Page "A propos" d'information contextuelles sur le projet 
 */

require_once('header.php');
require_once('constant.php'); 
?>



		<div id="main" class="wrapper clearfix">


			<aside>
				<h3>Auteur</h3>
				<ul>
						<li>Nom : Valentin CARRUESCO aka Idleman</li>
						<li>Mail : <a href="mailto: idleman@idleman.fr">idleman@idleman.fr</a></li>
						<li>Blog : <a href="http://blog.idleman.fr">blog.idleman.fr</a></li>
						<li>CV : <a href="http://www.idleman.fr">blog.idleman.fr</a></li>
				</ul>
			</aside>
			
			<article>
				<header>
					<h1>A propos</h1>
					<p>A propos de Leed (Light Feed)</p>
				</header>
				


				
				<section>
					<h2>G&eacute;n&eacute;ralit&eacute;s :</h2>
					<ul>
						<li><b>Application :</b> Leed (Light Feed)</li>
						<li><b>Version :</b> <?php echo VERSION_NUMBER.' ('.VERSION_NAME.')'; ?></li>
						<li><b>Auteur :</b> Valentin CARRUESCO aka Idleman (idleman@idleman.fr)</li>
						<li><b>D&eacute;pot SVN :</b> http://hades.idleman.fr/leed</li>
						<li><b>Licence :</b> CC by-nc-nd (http://creativecommons.org/licenses/by-nc-nd/2.0/fr/) Nb : les travaux d&eacute;riv&eacute;s peuvent &ecirc;tre autoris&eacute;s avec accord de l'auteur</li>
					</ul>

					<h2>PRESENTATION</h2>
					<p>
						<b>Leed (contraction de Light Feed)</b> est un agr&eacute;gatteur RSS libre et minimaliste qui permet la consultation de flux RSS de mani&egrave;re rapide et non intrusive.</p><p>
						Toutes les t&acirc;ches de traitement de flux sont effectu&eacute;es de mani&egrave;re invisible par une t&acirc;che synchronis&eacute;e (Cron), ainsi, l'utilisateur ne subit pas les lenteurs dues &agrave; la r&eacute;cup&eacute;ration et au traitement de chacun des flux suivis.</p><p>
						A noter que Leed est compatible toutes r&eacute;solutions, sur pc, tablettes et smartphone et fonctionne sous tous les navigateurs.</p><p>
						Le script est &eacute;galement compatible avec les fichiers d'exports/imports OPML ce qui rend la migration de tous les agr&eacute;gateurs r&eacute;spectant le standard OPML simple et rapide.
					</p>
					<h2>INSTALLATION</h2>
					<ul>
						<li>1. R&eacute;cuperez le projet sur le d&eacute;pot SVN de la version courante : http://hades.idleman.fr/leed</li>
						<li>2. Placez le projet dans votre r&eacute;pertoire web et appliquez une permission chmod 777 sur le dossier et son contenu</li>
						<li>3. Depuis votre navigateur, accedez &agrave; la page d'installation install.php (ex : http://votre.domaine.fr/leed/install.php) et suivez le instructions.</li>
						<li>4. Une fois l'installation termin&eacute;e, supprimez le fichier install.php par mesure de s&eacute;curit&eacute;</li>
						<li>5. Mettez en place un cron (sudo crontab -e pour ouvrir le fichier de cron) et placez y un appel vers la page http://votre.domaine.fr/leed/action.php?action=synchronize ex : <br/>

						<code>0 * * * * wget -q -O /var/www/leed/logsCron http://127.0.0.1/leed/action.php?action=synchronize</code><br/>

						Pour mettre &agrave; jour vos flux toutes les heures &agrave; la minute 0 (il est conseill&eacute; de ne pas mettre une fr&eacute;quence trop rapide pour laisser le temps au script de s'executer).
						<li>6. Le script est install&eacute;, merci d'avoir choisis Leed, l'agr&eacute;gatteur RSS libre et svelte :p.</li>
					</ul>
					<h2>LIBRAIRIES</h2>
					<ul>
						<li><b>Responsive / Cross browser :</b> Initializr (www.initializr.com)</li>
						<li><b>Javascript :</b> JQuery (www.jquery.com)</li>
					</ul>			
				</section>
				


			</article>
			
			
		</div> <!-- #main -->

<?php require_once('footer.php'); ?>
