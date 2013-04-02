<?php 

/*
 @nom: install
 @auteur: Idleman (idleman@idleman.fr)
 @description:  Page d'installation du script (a supprimer après installation)
 */

session_start(); 
require_once('Functions.class.php');
$_ = array_merge($_GET, $_POST);
$whiteList = array(
	/* La liste blanche recense les variables ne devant pas être passées via
	   la sécurisation, mais simplement échappées pour Php. */
	'mysqlHost', 'mysqlLogin', 'mysqlMdp', 'mysqlBase', 'mysqlPrefix',
);
foreach($_ as $key=>&$val){
 $val = in_array($key, $whiteList)
	? str_replace("'", "\'", $val)
	: Functions::secure($val);
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

	<link rel="stylesheet" href="templates/marigolds/css/style.css">

	<script src="templates/marigolds/js/libs/jqueryAndModernizr.min.js"></script>
</head>
<body>
<div class="global-wrapper">
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

	if (!Functions::testDb(
		$_['mysqlHost'], $_['mysqlLogin'], $_['mysqlMdp'], $_['mysqlBase']
	)) {
		///@TODO: faire un retour plus intelligible + tests dans le common.php
		echo "<p>Connexion à la base de donnnées impossible :</p>";
		echo "<ul>\n";
		echo "<li>host: {$_['mysqlHost']}\n";
		echo "<li>login: {$_['mysqlLogin']}\n";
		echo "<li>password: {$_['mysqlMdp']}\n";
		echo "<li>database: {$_['mysqlBase']}\n";
		echo "</ul><p><a href=''>Relancer l'installation</a></p>\n";
		die();
	}

	$constant = "<?php
	define('VERSION_NUMBER','1.5');
	define('VERSION_NAME','Beta');

	//Host de Mysql, le plus souvent localhost ou 127.0.0.1
	define('MYSQL_HOST','".$_['mysqlHost']."'); 
	//Identifiant MySQL
	define('MYSQL_LOGIN','".$_['mysqlLogin']."');
	//mot de passe MySQL
	define('MYSQL_MDP','".$_['mysqlMdp']."');
	//Nom de la base MySQL ou se trouvera leed
	define('MYSQL_BDD','".$_['mysqlBase']."');
	//Prefix des noms des tables leed pour les bases de données uniques
	define('MYSQL_PREFIX','".$_['mysqlPrefix']."');
	//Theme graphique
	define('DEFAULT_THEME','marigolds');
	//Nombre de pages affichées dans la barre de pagination
	define('PAGINATION_SCALE',5);
	//Nombre de flux mis à jour lors de la synchronisation graduée
	define('SYNC_GRAD_COUNT',10);	
	?>";

	file_put_contents('constant.php', $constant);

	require_once('constant.php');
	require_once('MysqlEntity.class.php');
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
	$root = (substr($_['root'], strlen($_['root'])-1)=='/'?$_['root']:$_['root'].'/');
	$synchronisationCode = substr(sha1(rand(0,30).time().rand(0,30)),0,10);

	$configurationManager->add('root',$root);
	//$configurationManager->put('view',$_['view']);
	$configurationManager->add('articleView',$_['articleView']);
	$configurationManager->add('articleDisplayContent',$_['articleDisplayContent']);
	$configurationManager->add('articleDisplayAnonymous',$_['articleDisplayAnonymous']);
	$configurationManager->add('articlePerPages',$_['articlePerPages']);
	$configurationManager->add('articleDisplayLink',$_['articleDisplayLink']);
	$configurationManager->add('articleDisplayDate',$_['articleDisplayDate']);
	$configurationManager->add('articleDisplayAuthor',$_['articleDisplayAuthor']);
	$configurationManager->add('synchronisationType',$_['synchronisationType']);
	$configurationManager->add('feedMaxEvents',$_['feedMaxEvents']);
	
	$configurationManager->add('synchronisationCode',$synchronisationCode);

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
					<h1>Installation de Leed terminée</h1>
					<p>L'installation de Leed est terminée !!</p>

					
					
					<?php if ($_['synchronisationType']=='auto'){ ?>
					<p>N'oubliez pas de mettre en place le CRON adapté pour que vos flux se mettent à jour, exemple :</p>
					<code>sudo crontab -e</code>
					<p>Dans le fichier qui s'ouvre ajoutez la ligne :</p>
					<code>0 * * * * wget --no-check-certificate -q -O <?php echo (str_replace(array(basename(__FILE__),'\\'),array('logs/cron.log','/'),__FILE__)); ?> "<?php echo $root ?>action.php?action=synchronize&code=<?php echo $synchronisationCode; ?>"	#Commande de mise a jour de leed</code>
					<p>Quittez et sauvegardez le fichier.</p>
					<p>Cet exemple mettra à jour vos flux toutes les heures et ajoutera le rapport de mise a jour sous le nom "cron.log" dans votre dossier leed</p>
	 				
					<?php }else if ($_['synchronisationType']=='graduate'){ ?>
					<p>N'oubliez pas de mettre en place le CRON adapté pour que vos flux se mettent à jour, exemple :</p>
					<code>sudo crontab -e</code>
					<p>Dans le fichier qui s'ouvre ajoutez la ligne :</p>
					<code>0,5,10,15,20,25,30,35,40,45,50,55 * * * * wget --no-check-certificate -q -O <?php echo (str_replace(array(basename(__FILE__),'\\'),array('logs/cron.log','/'),__FILE__)); ?> "<?php echo $root ?>action.php?action=synchronize&code=<?php echo $synchronisationCode; ?>"	#Commande de mise a jour de leed</code>
					<p>Quittez et sauvegardez le fichier.</p>
					<p>Cet exemple mettra à jour vos flux toutes les 5 minutes(conseillé pour une synchronisation graduée) et ajoutera le rapport de mise a jour sous le nom "cron.log" dans votre dossier leed</p>
	 				

					<?php }  ?>

					<p><h3>Important ! </h3>N'oubliez pas de supprimer la <b>page install.php</b> par mesure de sécurité</p>
	 				<p>Cliquez <a style="color:#F16529;" href="index.php">ici</a> pour acceder au script</p>
	 <?php
}else{
?>

			<aside>
				<h3 class="left">Verifications</h3> 
				<ul class="clear" style="margin:0">

						<?php 

						if(!is_writable('./')){
							$test['Erreur'][]='Écriture impossible dans le repertoire Leed, veuillez ajouter les permissions en ecriture sur tout le dossier (sudo chmod 775 -R '.str_replace(basename(__FILE__),'',__FILE__).')';
						}else{
							$test['Succès'][]='Permissions sur le dossier courant : OK';
						}

						if (!@function_exists('file_get_contents')){
							 $test['Erreur'][] = 'La fonction requise "file_get_contents" est inaccessible sur votre serveur, verifiez votre version de PHP.';
						}else{
							 $test['Succès'][] = 'Fonction requise "file_get_contents" : OK';	
						}
						if (!@function_exists('file_put_contents')){
							 $test['Erreur'][] = 'La fonction requise "file_put_contents" est inaccessible sur votre serveur, verifiez votre version de PHP.';
						}else{
							 $test['Succès'][] = 'Fonction requise "file_put_contents" : OK';	
						}
						if (@version_compare(PHP_VERSION, '5.1.0') <= 0){
						 $test['Erreur'][] = 'Votre version de PHP ('.PHP_VERSION.') est trop ancienne, il est possible que certaines fonctionalitees du script comportent des disfonctionnements.';
						}else{
						 $test['Succès'][] = 'Compabilité de version PHP ('.PHP_VERSION.') : OK';	
						}

						// if (!@extension_loaded('sqlite3')){
						//  $test['Erreur'][] = 'L\'Extension Sqlite3 n\'est pas activée sur votre serveur, merci de bien vouloir l\'installer';
						// }else{
						//  $test['Succès'][] = 'Extension Sqlite3 : OK';	
						// }

						if(ini_get('safe_mode') && ini_get('max_execution_time')!=0){
							$test['Erreur'][] = 'Le script ne peux pas gerer le timeout tout seul car votre safe mode est activé,<br/> dans votre fichier de configuration PHP, mettez la variable max_execution_time à 0 ou désactivez le safemode.';
						}else{
							$test['Succès'][] = 'Gestion du timeout : OK';
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
					<p>Merci de prendre quelques instants pour vérifier les infos ci dessous :</p>
				
				</header>
			
				<section>
					<h2>Général</h2>
					<p>Racine du projet : <input type="text" name="root" value="<?php echo str_replace(basename(__FILE__),'','http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>"></p>
					<h3 class="articleDetails">Laissez bien un "/" en fin de chaine ex : http://monsite.com/leed/</h3>
				</section>

				<section>
					<h2>Base de données</h2>
					<p>Hote MySQL : <input type="text" name="mysqlHost" value=""></p>
					<h3 class="articleDetails">Géneralement localhost</h3>
					<p>Identifiant MySQL : <input type="text" name="mysqlLogin" value=""></p>
					<p>Mot de passe MySQL : <input type="text" autocomplete="off" name="mysqlMdp" value=""></p>
					<p>Nom de base MySQL : <input type="text" name="mysqlBase" value=""></p>
					<h3 class="articleDetails">Nom de la base de données vouée à Leed (à créer avant d'installer leed)</h3>
					<p>Prefixe des tables : <input type="text" name="mysqlPrefix" value="leed_"></p>
				</section>

				
				<section>
					<h2>Administrateur</h2>
					<p>Identifiant de l'administrateur: <input type="text" name="login" placeholder="Identifiant"></p>
					<p>Mot de passe de l'administrateur: <input type="text" autocomplete="off" name="password" placeholder="Mot de passe"></p>
				</section>

				<section>
					<h2>Synchronisation</h2>
					<p><input type="radio" checked="checked" value="auto" name="synchronisationType"> <strong>Automatique (complet) :</strong> Le script mettra à jour automatiquement tous vos flux en une seule fois, ceci permet la mise à jour en une fois de tous vos flux mais peux faire ramer votre serveur, les appels cron ne doivent pas être trop rapprochés.</p>
					<p><input type="radio"  value="graduate" name="synchronisationType"> <strong>Automatique (gradué) : </strong>Le script mettra à jour automatiquement les 10 flux les plus vieux en terme de mise à jour, ceci permet d'alléger la charge serveur et d'éviter les timeouts intempestifs mais nécessite un appel de cron plus fréquent afin de mettre à jour le plus de flux possible.</p>
					<p><input type="radio"  value="manual" name="synchronisationType"> <strong>Manuel (complet) : </strong>Le script ne fait aucune mise à jour automatique, vous devez faire vous même les mises à jour depuis l'espace administration.</p>
				</section>

				<section>
					<h2>Préferences</h2>
					<p>Autoriser la lecture anonyme: <input type="radio" checked="checked" value="1" name="articleDisplayAnonymous">Oui <input type="radio" value="0" name="articleDisplayAnonymous">Non</p>
					<h3 class="articleDetails">Nb: si vous choisissez cette option, les utilisateurs non authentifiés pourront consulter vos flux (sans pouvoir les marquer comme lu/non lu).</h3>
					<p>Nombre d'articles par page : <input type="text" value="5" name="articlePerPages"></p>
					<p>Affichage du lien direct de l'article: <input type="radio" checked="checked" value="1" name="articleDisplayLink">Oui <input type="radio" value="0" name="articleDisplayLink">Non</p>
					<p>Affichage de la date de l'article: <input type="radio" checked="checked" value="1" name="articleDisplayDate">Oui <input type="radio" value="0" name="articleDisplayDate">Non</p>
					<p>Affichage de l'auteur de l'article: <input type="radio" checked="checked" value="1" name="articleDisplayAuthor">Oui <input type="radio" value="0" name="articleDisplayAuthor">Non</p>
					<p>Affichage du contenu de l'article: <input type="radio" checked="checked" value="1" name="articleDisplayContent">Oui <input type="radio" value="0" name="articleDisplayContent">Non</p>
					<p>Type d'affichage du contenu: <input type="radio" checked="checked" value="partial" name="articleView">Partiel <input type="radio" value="complete" name="articleView">Complet</p>
					<h3 class="articleDetails">Nb: si vous choissisez un affichage partiel des articles, un click sur ces derniers menera à l'article sur le blog de l'auteur.</h3>
					<p>Catégorie par defaut: <input type="text" value="Géneral" name="category"></p>
					<p>Conserver les <input type="text" value="30" name="feedMaxEvents"> derniers événement d'un flux</p>
					<h3 class="articleDetails">Nb: Plus il y aura d'événements à conserver, plus votre base de données sera importante. Nous vous conseillons de garder les 50 derniers événements au maximum pour conserver une performance correcte.<br>Notez que vos événements marqués comme favoris ne seront jamais supprimés.</h3>
					
				</section>

	


				<button name="installButton">Lancer l'installation</button>
			</article>
	</form>		
	<?php }else{ ?>
	<p>Il vous manque des prérequis pour continuer l'installation, référez vous au panneau de droite.</p>
	<?php }?>		
	<?php } ?>
		</div> <!-- #main -->


	</div> <!-- #main-container -->

	<div id="footer-container">
		<footer class="wrapper">
			<p>Leed "Light Feed" by <a target="_blank" href="http://blog.idleman.fr">Idleman</a></p>
		</footer>
	</div>
</div>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="js/libs/jquery-1.7.2.min.js"><\/script>')</script>

<script src="templates/marigolds/js/script.js"></script>
</body>
</html>
