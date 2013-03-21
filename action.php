<?php

/*
 @nom: action
 @auteur: Idleman (idleman@idleman.fr)
 @description: Page de gestion des évenements non liés a une vue particulière (appels ajax, requetes sans resultats etc...)
 */

if(!ini_get('safe_mode')) @set_time_limit(0);
require_once("common.php");



//Execution du code en fonction de l'action
switch ($_['action']){

	case 'synchronize':
		if (ob_get_level() == 0) ob_start();
		require_once("SimplePie.class.php");

		
		echo '<link rel="stylesheet" href="templates/marigolds/css/style.css"><ul style="font-family:Verdana;">';
		echo str_pad('',4096)."\n";ob_flush();flush();

		if (isset($_['code']) && $configurationManager->get('synchronisationCode')!=null && $_['code'] == $configurationManager->get('synchronisationCode')){

		$synchronisationType = $configurationManager->get('synchronisationType');
		$maxEvents = $configurationManager->get('feedMaxEvents');

		

			echo '<h3>Synchronisation du '.date('d/m/Y H:i:s').'</h3>';
			echo '<hr/>';
			echo str_pad('',4096)."\n";ob_flush();flush();

		if($synchronisationType=='graduate'){
			$feeds = $feedManager->loadAll(null,'lastupdate','10');
			echo 'Type gradué...<br/>';
			echo str_pad('',4096)."\n";ob_flush();flush();
		}else{
			$feeds = $feedManager->populate('name');
			echo 'Type complet...<br/>';
			echo str_pad('',4096)."\n";ob_flush();flush();
		}	
			
			echo count($feeds).' Flux à synchroniser...<br/>';
			echo str_pad('',4096)."\n";ob_flush();flush();
		foreach ($feeds as $feed) {
			echo date('H:i:s').' - Flux '.$feed->getName().' ('.$feed->getUrl().') parsage des flux...<br/>';
			echo str_pad('',4096)."\n";ob_flush();flush();
			$feed->parse();
			echo str_pad('',4096)."\n";ob_flush();flush();
			echo date('H:i:s').' - Flux '.$feed->getName().' ('.$feed->getUrl().') supression des vieux evenements...<br/>';
			echo str_pad('',4096)."\n";ob_flush();flush();
			if($maxEvents!=0) $feed->removeOldEvents($maxEvents);
			echo date('H:i:s').' - Flux '.$feed->getName().' ('.$feed->getUrl().') terminé<br/>';
			echo str_pad('',4096)."\n";ob_flush();flush();
		}
			echo date('H:i:s').' - Synchronisation terminée ( '.number_format(microtime(true)-$start,3).' secondes )<br/>';
		echo str_pad('',4096)."\n";ob_flush();flush();

	}else{
		echo 'Code de synchronisation incorrect ou non spécifié';
	}

		ob_end_flush();

	break;


	case 'readAll':
		if($myUser==false) exit('Vous devez vous connecter pour cette action.');
		$whereClause = array();
		$whereClause['unread'] = '1';
		if(isset($_['feed']))$whereClause['feed'] = $_['feed'];
		$eventManager->change(array('unread'=>'0'),$whereClause);
		header('location: ./index.php');
	break;

	case 'readFolder':
		if($myUser==false) exit('Vous devez vous connecter pour cette action.');

		$feeds = $feedManager->loadAllOnlyColumn('id',array('folder'=>$_['folder']));
		
		foreach($feeds as $feed){
			$eventManager->change(array('unread'=>'0'),array('feed'=>$feed->getId()));
		}

		header('location: ./index.php');

	break;

	case 'updateConfiguration':
		if($myUser==false) exit('Vous devez vous connecter pour cette action.');


			//Ajout des préférences et réglages
			$configurationManager->put('root',(substr($_['root'], strlen($_['root'])-1)=='/'?$_['root']:$_['root'].'/'));
			//$configurationManager->put('view',$_['view']);
			$configurationManager->put('articleView',$_['articleView']);
			$configurationManager->put('articleDisplayContent',$_['articleDisplayContent']);
			$configurationManager->put('articleDisplayAnonymous',$_['articleDisplayAnonymous']);

			$configurationManager->put('articlePerPages',$_['articlePerPages']);
			$configurationManager->put('articleDisplayLink',$_['articleDisplayLink']);
			$configurationManager->put('articleDisplayDate',$_['articleDisplayDate']);
			$configurationManager->put('articleDisplayAuthor',$_['articleDisplayAuthor']);
			$configurationManager->put('plugin_shaarli',(isset($_['plugin_shaarli']) && $_['plugin_shaarli']=='on'?1:0));
			$configurationManager->put('plugin_shaarli_link',$_['plugin_shaarli_link']);
			$configurationManager->put('synchronisationType',$_['synchronisationType']);
			$configurationManager->put('feedMaxEvents',$_['feedMaxEvents']);

	
		
			 $userManager->change(array('login'=>$_['login']),array('id'=>$myUser->getId()));
			 if(trim($_['password'])!='') $userManager->change(array('password'=>User::encrypt($_['password'])),array('id'=>$myUser->getId()));
		


	header('location: ./settings.php');
	break;


	case 'purge':
		if($myUser==false) exit('Vous devez vous connecter pour cette action.');
		$eventManager->truncate();
		header('location: ./settings.php');
	break;


	case 'exportFeed':
			if($myUser==false) exit('Vous devez vous connecter pour cette action.');
				/*********************/
			/** Export **/
			/*********************/
			if(isset($_POST['exportButton'])){
				$opml = new Opml();
				$xmlStream = $opml->export();

				header('Content-Description: File Transfer');
			    header('Content-Type: application/octet-stream');
			    header('Content-Disposition: attachment; filename=leed-'.date('d-m-Y').'.opml');
			    header('Content-Transfer-Encoding: binary');
			    header('Expires: 0');
			    header('Cache-Control: must-revalidate');
			    header('Pragma: public');
			    header('Content-Length: ' . strlen($xmlStream));
			    /*
				//A decommenter dans le cas ou on a des pb avec ie
				if(preg_match('/msie|(microsoft internet explorer)/i', $_SERVER['HTTP_USER_AGENT'])){
				  header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				  header('Pragma: public');
				}else{
				  header('Pragma: no-cache');
				}
			    */
			    ob_clean();
			    flush();
			    echo $xmlStream;
			}
	break;
	

	case 'importForm':
		if($myUser==false) exit('Vous devez vous connecter pour cette action.');
		echo '<link rel="stylesheet" href="templates/marigolds/css/style.css"><form action="action.php?action=importFeed" method="POST" enctype="multipart/form-data"><h2>Importer les flux au format opml</h2>
					<p>Fichier OPML : <input name="newImport" type="file"/> <button name="importButton">Importer</button></p>
					<p>Nb : L\'importation peux prendre un certain temps, laissez votre navigateur tourner et allez vous prendre un café :).</p></form>
				
			';
	break;

	case 'synchronizeForm':
	 if(isset($myUser) && $myUser!=false){  
		echo '<link rel="stylesheet" href="templates/marigolds/css/style.css">
				<a class="button" href="action.php?action=synchronize&format=html&code='.$configurationManager->get('synchronisationCode').'">Synchroniser maintenant</a>
					<p>Nb : La synchronisation peux prendre un certain temps, laissez votre navigateur tourner et allez vous prendre un café :).</p>
				
			';
		}else{
			echo 'Vous devez être connecté pour accéder à cette partie.';
		}

	break;

	case 'changeFolderState':
		if($myUser==false) exit('Vous devez vous connecter pour cette action.');
		$folderManager->change(array('isopen'=>$_['isopen']),array('id'=>$_['id']));
	break;

	case 'importFeed':
		echo '<link rel="stylesheet" href="templates/marigolds/css/style.css">
		<style>
		a{
			color: #F16529;
		}
		</style>';
		if($myUser==false) exit('Vous devez vous connecter pour cette action.');
		if(!isset($_POST['importButton'])) break;
		$opml = new Opml();
		echo "<h3>Importation</h3><p>En cours...</p>\n";
		$errorOutput = $opml->import($_FILES['newImport']['tmp_name']);
		if (empty($errorOutput)) {
			echo "<p>L'import s'est déroulé sans problème.</p>\n";
		} else {
			echo "<p>Erreurs à l'importation!</p>\n";
			foreach($errorOutput as $line) {
				echo "<p>$line</p>\n";
			}
		}
		if (!empty($opml->alreadyKnowns)) {
			echo "<h3>Certains flux étaient déjà connus, ils n'ont pas été "
				."réimportés&nbsp;:</h3>\n<ul>\n";
			foreach($opml->alreadyKnowns as $alreadyKnown) {
				foreach($alreadyKnown as &$elt) $elt = htmlspecialchars($elt);
				$maxLength = 80;
				$delimiter = '...';
				if (strlen($alreadyKnown->description)>$maxLength) {
					$alreadyKnown->description =
						substr($alreadyKnown->description, 0,
							$maxLength-strlen($delimiter)
						).$delimiter;
				}
				echo "<li><a target='_parent' href='{$alreadyKnown->xmlUrl}'>"
					."{$alreadyKnown->description}</a></li>\n";
			}
			echo "</ul>\n";
		}
		$syncCode = $configurationManager->get('synchronisationCode');
		assert('!empty($syncCode)');
		$syncLink = "action.php?action=synchronize&format=html&code=$syncCode";
		echo "<p>";
		echo "<a href='$syncLink' style='text-decoration:none;font-size:3em'>"
			."↺</a>";
		echo "<a href='$syncLink'>Cliquez ici pour synchroniser vos flux importés.</a>";
		echo "<p>\n";
	break;

	
	case 'addFeed':
			require_once("SimplePie.class.php");
			if($myUser==false) exit('Vous devez vous connecter pour cette action.');
			if(isset($_['newUrl'])){
				$newFeed = new Feed();
				$newFeed->setUrl($_['newUrl']);
				$newFeed->getInfos();
				$newFeed->setFolder((isset($_['newUrlCategory'])?$_['newUrlCategory']:1));
				$newFeed->save();
				$newFeed->parse();
				header('location: ./settings.php#defaultFolder');
			}
	break;

	case 'changeFeedFolder':
		if($myUser==false) exit('Vous devez vous connecter pour cette action.');
		if(isset($_['feed'])){
			$feedManager->change(array('folder'=>$_['folder']),array('id'=>$_['feed']));
		}
		header('location: ./settings.php');
	break;

	case 'removeFeed':
		if($myUser==false) exit('Vous devez vous connecter pour cette action.');
		if(isset($_GET['id'])){
			$feedManager->delete(array('id'=>$_['id']));
			$eventManager->delete(array('feed'=>$_['id']));
		}
		header('location: ./settings.php');
	break;

	case 'addFolder':
		if($myUser==false) exit('Vous devez vous connecter pour cette action.');
		if(isset($_['newFolder'])){
				$folder = new Folder();
			if($folder->rowCount(array('name'=>$_['newFolder']))==0){

				$folder->setParent(-1);
				$folder->setIsopen(0);
				$folder->setName($_['newFolder']);
				$folder->save();
			}
		}
		header('location: ./settings.php');
	break;


	case 'renameFolder':
		if($myUser==false) exit('Vous devez vous connecter pour cette action.');
		if(isset($_['id'])){
			$folderManager->change(array('name'=>$_['name']),array('id'=>$_['id']));
		}
	break;

	case 'renameFeed':
		if($myUser==false) exit('Vous devez vous connecter pour cette action.');
		if(isset($_['id'])){
			$feedManager->change(array('name'=>$_['name']),array('id'=>$_['id']));
		}
	break;

	case 'removeFolder':
		if($myUser==false) exit('Vous devez vous connecter pour cette action.');
		if(isset($_['id']) && is_numeric($_['id']) && $_['id']>0){
			$eventManager->customExecute('DELETE FROM '.MYSQL_PREFIX.'event WHERE '.MYSQL_PREFIX.'event.feed in (SELECT '.MYSQL_PREFIX.'feed.id FROM '.MYSQL_PREFIX.'feed WHERE '.MYSQL_PREFIX.'feed.folder =\''.intval($_['id']).'\') ;');
			$feedManager->delete(array('folder'=>$_['id']));
			$folderManager->delete(array('id'=>$_['id']));
		}
		header('location: ./settings.php');
	break;

	case 'readContent':
		if(isset($_['id'])){
			$event = $eventManager->load(array('id'=>$_['id']));
			if($myUser!=false) $eventManager->change(array('unread'=>'0'),array('id'=>$_['id']));
		}
	break;

	case 'unreadContent':
		$event = $eventManager->load(array('id'=>$_['id']));
		if($myUser!=false) $eventManager->change(array('unread'=>'1'),array('id'=>$_['id']));
	break;

	case 'addFavorite':
		if($myUser==false) exit('Vous devez vous connecter pour cette action.');
		$eventManager->change(array('favorite'=>'1'),array('id'=>$_['id']));
	break;

	case 'removeFavorite':
		if($myUser==false) exit('Vous devez vous connecter pour cette action.');
		$eventManager->change(array('favorite'=>'0'),array('id'=>$_['id']));
	break;
	
	case 'login':
	
		if(isset($_['usr'])){
			$user = $userManager->existAuthToken($_['usr']);
			if($user==false){
				exit("erreur identification : le compte est inexistant");
			}else{
				$_SESSION['currentUser'] = serialize($user);
				header('location: ./action.php?action=addFeed&newUrl='.$_['newUrl']);
			}
		}else{
				$user = $userManager->exist($_['login'],$_['password']);
			if($user==false){
				exit("erreur identification : le compte est inexistant");
			}else{
				$_SESSION['currentUser'] = serialize($user);
			}
			header('location: ./index.php');	
		}
		

	
	break;

	
	case 'logout':
		$_SESSION = array();
		session_unset();
		session_destroy();
		header('location: ./index.php');
	break;
	
	default:
		exit('0');
	break;
}


?>
