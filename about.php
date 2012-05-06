<?php require_once('header.php'); ?>



		<div id="main" class="wrapper clearfix">


			<aside>
				<h3>Auteur</h3>
				<ul>
						<li>Nom : Valentin CARRUESCO aka Idleman</li>
						<li>Mail : <a href="idleman@idleman.fr">idleman@idleman.fr</a></li>
						<li>Blog : <a href="blog.idleman.fr">blog.idleman.fr</a></li>
						<li>CV : <a href="www.idleman.fr">blog.idleman.fr</a></li>
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
						<li><b>Version :</b> 1.0 Alpha</li>
						<li><b>Auteur :</b> Valentin CARRUESCO aka Idleman (idleman@idleman.fr)</li>
						<li><b>Dépot SVN :</b> http://hades.idleman.fr/leed</li>
						<li><b>Licence :</b> CC by-nc-nd (http://creativecommons.org/licenses/by-nc-nd/2.0/fr/) Nb : les traveaux dérivé peuvent être autorisés avec accord de l'auteur</li>
					</ul>

					<h2>PRESENTATION</h2>
					<p>
						<b>Leed (contraction de Light Feed)</b> est un agrégatteur RSS libre et minimaliste qui permet la consultation de flux RSS de manière rapide et non intrusive.</p><p>
						Toutes les tâches de traitements de flux sont effectuées de manière invisible par une tâche synchronisée (Cron),ainsi, l'utilisateur ne subis pas les lenteures dues à la récupération et au traitement de chacun des flux suivis.</p><p>
						A noter que Leed est compatible toutes résolutions, sur pc, tablettes et smartphone et fonctionne sous tous les navigateurs.</p><p>
						Le script est également compatible avec les fichiers d'exports/imports OPML ce qui rend la migration de tous les agrégateurs réspectant le standard OPML simple et rapide.
					</p>
					<h2>INSTALLATION</h2>
					<ul>
						<li>1. Récuperez le projet sur le dépot SVN de la version courante : http://hades.idleman.fr/leed</li>
						<li>2. Placez le projet dans votre repertoire web et appliquer une permission chmod 777 sur le dossier et son contenu</li>
						<li>3. Depuis votre navigateur, accedez a la page d'installation install.php (ex : http://votre.domaine.fr/leed/install.php) et suivez le instructions.</li>
						<li>4. Une fois l'installation terminée, supprimez le fichier install.php par mesure de sécurité</li>
						<li>5. Mettez en place un cron (sudo crontab -e pour ouvrir le fichier de cron) et placez y un appel vers la page http://votre.domaine.fr/leed/action.php?action=synchronize ex : <br/>

						<code>0 * * * * wget -q -O /var/www/leed/logsCron http://127.0.0.1/leed/action.php?action=synchronize</code><br/>

						Pour mettre a jour vos flux toutes les heures à la minute 0 (il est conseillé de ne pas mettre une fréquence trop rapide pour laisser le temps au script de s'executer).
						<li>6. Le script est installé, merci d'vaoir choisis Leed, l'agrégatteur RSS libre et svelte :p.</li>
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
