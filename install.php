<?php 

/*
 @nom: install
 @auteur: Idleman (idleman@idleman.fr)
 @description:  Page d'installation du script (a supprimer après installation)
 */

session_start(); 
require_once('Functions.class.php');
$_ = array();
foreach($_POST as $key=>$val){
$_[$key]=Functions::secure($val);
}
foreach($_GET as $key=>$val){
$_[$key]=Functions::secure($val);
}
?>


<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

	<title></title>
	<meta name="description" content="">
	<meta name="author" content="">

	<meta name="viewport" content="width=device-width">

	<link rel="stylesheet" href="css/style.css">

	<script src="js/libs/jqueryAndModernizr.min.js"></script>
</head>
<body>
	<div id="header-container">
		<header class="wrapper clearfix">
			<h1 class="logo" id="title"><a href="./index.php">L<i>eed</i></a></h1>
			<nav>
			</nav>
		</header>
	</div>


	<div id="main-container">

<div id="main" class="wrapper clearfix">



			<?php


//Récuperation et sécurisation de toutes les variables POST et GET


if(isset($_['installButton'])){
	require_once('SQLiteEntity.class.php');
	require_once('Feed.class.php');
	require_once('Event.class.php');
	
	require_once('User.class.php');
	require_once('Folder.class.php');
	require_once('Configuration.class.php');
	$myUser = (isset($_SESSION['currentUser'])?unserialize($_SESSION['currentUser']):false);
	$feedManager = new Feed();
	$eventManager = new Event();
	$userManager = new User();
	$folderManager = new Folder();
	$configurationManager = new Configuration();

	//Création de la base et des tables
	$feedManager->create();
	$eventManager->create();
	$userManager->create();
	$folderManager->create();
	$configurationManager->create();
	//Ajout de l'administrateur
	$admin = new User();
	$admin->setLogin($_['login']);
	$admin->setPassword($_['password']);
	$admin->save();
	//Identification de l'utilisateur en session
	$_SESSION['currentUser'] = serialize($admin);
	//Ajout des préférences et reglages
	$configurationManager->add('root',(substr($_['root'], strlen($_['root'])-1)=='/'?$_['root']:$_['root'].'/'));
	//$configurationManager->put('view',$_['view']);
	$configurationManager->add('articleView',$_['articleView']);
	$configurationManager->add('articleDisplayContent',$_['articleDisplayContent']);
	$configurationManager->add('articleDisplayAnonymous',$_['articleDisplayAnonymous']);
	$configurationManager->add('articlePerPages',$_['articlePerPages']);
	$configurationManager->add('articleDisplayLink',$_['articleDisplayLink']);
	$configurationManager->add('articleDisplayDate',$_['articleDisplayDate']);
	$configurationManager->add('articleDisplayAuthor',$_['articleDisplayAuthor']);
	$configurationManager->add('plugin_shaarli',(isset($_['plugin_shaarli']) && $_['plugin_shaarli']=='on'?1:0));
	$configurationManager->add('plugin_shaarli_link',$_['plugin_shaarli_link']);
	$configurationManager->add('synchronisationType',$_['synchronisationType']);
	$configurationManager->add('feedMaxEvents',$_['feedMaxEvents']);


	//Création du dossier de base
	$folder = $folderManager->load(array('id'=>1));
	$folder = (!$folder?new Folder():$folder);
	$folder->setName($_['category']);
	$folder->setParent(-1);
	$folder->setIsopen(1);
	$folder->save();
	

?>

	 <article style="width:100%;">
				<header>
					<h1>Installation de Leed termin&eacute;e</h1>
					<p>L'installation de Leed est termin&eacute;e!!</p>

					
					
					<?php if ($_['synchronisationType']=='auto'){ ?>
					<p>N'oubliez pas de mettre en place le CRON adapt&eacute; pour que vos flux se mettent &agrave; jour, exemple :</p>
					<code>sudo crontab -e</code>
					<p>Dans le fichier qui s'ouvre ajoutez la ligne :</p>
					<code>0 * * * * wget -q -O <?php echo (str_replace(basename(__FILE__),'logs/cron.log',__FILE__)); ?> http://127.0.0.1/leed/action.php?action=synchronize	#Commande de mise a jour de leed</code>
					<p>Quittez et sauvegardez le fichier.</p>
					<p>Cet exemple mettra &agrave; jour vos flux toutes les heures et ajoutera le rapport de mise a jour sous le nom "logsCron" dans votre dossier leed</p>
	 				
					<?php }else if ($_['synchronisationType']=='graduate'){ ?>
					<p>N'oubliez pas de mettre en place le CRON adapt&eacute; pour que vos flux se mettent &agrave; jour, exemple :</p>
					<code>sudo crontab -e</code>
					<p>Dans le fichier qui s'ouvre ajoutez la ligne :</p>
					<code>0,5,10,15,20,25,30,35,40,45,50,55 * * * * wget -q -O <?php echo (str_replace(basename(__FILE__),'logs/cron.log',__FILE__)); ?> http://127.0.0.1/leed/action.php?action=synchronize	#Commande de mise a jour de leed</code>
					<p>Quittez et sauvegardez le fichier.</p>
					<p>Cet exemple mettra &agrave; jour vos flux toutes les 5 minutes(conseill&eacute; pour une synchronisation gradu&eacute;e) et ajoutera le rapport de mise a jour sous le nom "logsCron" dans votre dossier leed</p>
	 				

					<?php }  ?>

					<p>N'oubliez pas de supprimer la page install.php par mesure de s&eacute;curit&eacute;</p>
	 				<p>Cliquez <a style="color:#F16529;" href="index.php">ici</a> pour acceder au script</p>
	 <?php
}else{
?>

			<aside>
				<h3 class="left">Verifications</h3> 
				<ul class="clear" style="margin:0">

						<?php 

						if(!is_writable('./')){
							$test['Erreur'][]='Ecriture impossible dans le repertoire Leed, veuillez ajouter les permissions en ecriture sur tous le dossier (sudo chmod 775 -R '.str_replace(basename(__FILE__),'',__FILE__).')';
						}else{
							$test['Succ&egrave;s'][]='Permissions sur le dossier courant : OK';
						}

						if (!@function_exists('file_get_contents')){
							 $test['Erreur'][] = 'La fonction requise "file_get_contents" est inaccessible sur votre serveur, verifiez votre version de PHP.';
						}else{
							 $test['Succ&egrave;s'][] = 'Fonction requise "file_get_contents" : OK';	
						}
						if (!@function_exists('file_put_contents')){
							 $test['Erreur'][] = 'La fonction requise "file_put_contents" est inaccessible sur votre serveur, verifiez votre version de PHP.';
						}else{
							 $test['Succ&egrave;s'][] = 'Fonction requise "file_put_contents" : OK';	
						}
						if (@version_compare(PHP_VERSION, '5.3.0') <= 0){
						 $test['Erreur'][] = 'Votre version de PHP ('.PHP_VERSION.') est trop ancienne, il est possible que certaines fonctionalitees du script comportent des disfonctionnements.';
						}else{
						 $test['Succ&egrave;s'][] = 'Compabilit&eacute; de version PHP ('.PHP_VERSION.') : OK';	
						}

						if (!@extension_loaded('sqlite3')){
						 $test['Erreur'][] = 'L\'Extension Sqlite3 n\'est pas activ&eacute;e sur votre serveur, merci de bien vouloir l\'installer';
						}else{
						 $test['Succ&egrave;s'][] = 'Extension Sqlite3 : OK';	
						}

						if(ini_get('safe_mode') && ini_get('max_execution_time')!=0){
							$test['Erreur'][] = 'Le script ne peux pas gerer le timeout tout seul car votre safe mode est activ&eacute;,<br/> dans votre fichier de configuration PHP, mettez la variable max_execution_time à 0 ou désactivez le safemode.';
						}else{
							$test['Succ&egrave;s'][] = 'Gestion du timeout : OK';
						}

						foreach($test as $type=>$messages){
						?>
						<li style="font-size:10px;color:#ffffff;background-color:<?php echo ($type=='Erreur'?'#F16529':'#008000'); ?>"><?php echo $type; ?> :<ul><?php foreach($messages as $message){?><li style="border:1px solid #212121"><?php echo $message; ?></li><?php } ?></ul></li><li>&nbsp;</li>
						<?php } ?>
				</ul>
			</aside>

	<?php  if(!isset($test['Erreur'])){ ?>		
	<form action="install.php" method="POST">
			<article>
				<header>
					<h1>Installation de Leed</h1>
					<p>Merci de prendre quelques instants pour v&eacute;rifier les infos ci dessous :</p>
				
				</header>
			
				<section>
					<h2>G&eacute;n&eacute;ral</h2>
					<p>Racine du projet : <input type="text" name="root" value="<?php echo str_replace(basename(__FILE__),'','http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>"></p>
					<h3>Laissez bien un "/" en fin de chaine ex : http://monsite.com/leed/</h3>
				</section>

				<section>
					<h2>Administrateur</h2>
					<p>Identifiant de l'administrateur: <input type="text" name="login" placeholder="Identifiant"></p>
					<p>Mot de passe de l'administrateur: <input type="text" name="password" placeholder="Mot de passe"></p>
					<h3>Si vous perdez vos identifiants admin, supprimez le fichier database.db pour reinitialiser le script (nb: l'ensemble des donn&eacute;es seront perdues)</h3>
				</section>

				<section>
					<h2>Synchronisation</h2>
					<p><input type="radio" checked="checked" value="auto" name="synchronisationType"> <strong>Automatique (complet) :</strong> Le script mettra à jour automatiquement tous vos flux en une seule fois, ceci permet la mise &agrave; jour en une foix de tous vos flux mais peux faire ramer votre serveur, les appels cron ne doivent pas être trop rapproch&eacute;s</p>
					<p><input type="radio"  value="graduate" name="synchronisationType"> <strong>Automatique (gradu&eacute;) :</strong> Le script mettra à jour automatiquement les 10 flux les plus vieux en terme de mise &agrave; jour, ceci permet d'alleger la charge serveur et d'eviter els timeout intempestif mais necessiteun appel de cron plus fréquent afin de mettre à jour le plus de flux possible</p>
					<p><input type="radio"  value="manual" name="synchronisationType"> <strong>Manuel (complet) :</strong> Le script ne fait aucune mise à jour automatique, vous devez faire vous même les mises &agrave; jour depuis l'espace administration.</p>
				</section>

				<section>
					<h2>Pr&eacute;ferences</h2>
					<p>Autoriser la lecture anonyme: <input type="radio" checked="checked" value="1" name="articleDisplayAnonymous">Oui <input type="radio" value="0" name="articleDisplayAnonymous">Non</p>
					<h3>Nb: si vous choisissez cette option, les utilisateurs non authentifi&eacute; pourront consulter vos flux (sans pouvoir les marquer comme lu/non lu)</h3>
					<p>Nombre d'articles par pages: <input type="text" value="5" name="articlePerPages"></p>
					<p>Affichage du lien direct de l'article: <input type="radio" checked="checked" value="1" name="articleDisplayLink">Oui <input type="radio" value="0" name="articleDisplayLink">Non</p>
					<p>Affichage de la date de l'article: <input type="radio" checked="checked" value="1" name="articleDisplayDate">Oui <input type="radio" value="0" name="articleDisplayDate">Non</p>
					<p>Affichage de l'auteur de l'article: <input type="radio" checked="checked" value="1" name="articleDisplayAuthor">Oui <input type="radio" value="0" name="articleDisplayAuthor">Non</p>
					<p>Affichage du contenu de l'article: <input type="radio" checked="checked" value="1" name="articleDisplayContent">Oui <input type="radio" value="0" name="articleDisplayContent">Non</p>
					<p>Type d'affichage du contenu: <input type="radio" checked="checked" value="partial" name="articleView">Partiel <input type="radio" value="complete" name="articleView">Complet</p>
					<h3>Nb: si vous choissisez un affichage partiel des articles, un click sur ces derniers menera à l'article sur le blog de l'auteur.</h3>
					<p>Cat&eacute;gorie par defaut: <input type="text" value="General" name="category"></p>
					<p>Conserver les <input type="text" value="30" name="feedMaxEvents"> derniers &eacute;venement d'un flux</p>
					<h3>Nb: Plus il y aura d'&eacute;venements &agrave; conserver, plus votre abse de données sera importante. Nous vous conseillons de garder les 50 derniers evenements maximums pour conserver une performance correcte.<br>Notez que vos &eacute;venements marqu&eacute;s comme favoris ne seront jamais supprim&eacute;s</h3>
					
				</section>

				<section>
					<h2>Options</h2>
					<p><input onchange="$('.shaarliBlock').slideToggle(200);" type="checkbox" name="plugin_shaarli"> Activer le partage direct avec <a target="_blank" href="http://sebsauvage.net/wiki/doku.php?id=php:shaarli">shaarli<a></p>
					<p class="shaarliBlock" style="display:none;">Lien vers votre shaarli: <input style="width:100%;" type="text" placeholder="http://mon.domaine.com/shaarli/" name="plugin_shaarli_link"></p>
					<h3>Nb: cette option affichera un bouton a coté de chaque news pour vous proposer de la partager/stocker sur le gestionnaire de lien shaarli.</h3>
				</section>


				<button name="installButton">Lancer l'installation</button>
			</article>
	</form>		
	<?php }else{ ?>
	<p>Il vous manque des pr&eacute;requis pour continuer l'installation, r&eacute;f&eacute;rez vous au panneau de droite.</p>
	<?php }?>		
	<?php } ?>
		</div> <!-- #main -->


	</div> <!-- #main-container -->

	<div id="footer-container">
		<footer class="wrapper">
			<p>Leed "Light Feed" by <a target="_blank" href="http://blog.idleman.fr">Idleman</a></p>
		</footer>
	</div>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="js/libs/jquery-1.7.2.min.js"><\/script>')</script>

<script src="js/script.js"></script>
</body>
</html>
