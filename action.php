<?php
set_time_limit(0);
require_once("common.php");



//Execution du code en fonction de l'action
switch ($_['action']){

	case 'synchronize':

		$synchronisationType = $configurationManager->get('synchronisationType');
		$maxEvents = $configurationManager->get('feedMaxEvents');

		if(isset($_['format'])) echo '<textarea style="width:100%;height: 500px;overflow:auto;">';
			echo '------------------------------------------------------------------'."\n";
			echo '-------------- Synchronisation du '.date('d/m/Y H:i:s').' --------------'."\n";
			echo '------------------------------------------------------------------'."\n";

		if($synchronisationType=='graduate'){
			$feeds = $feedManager->loadAll(null,'lastupdate','10');
			echo 'Synchronisation graduée...'."\n";
		}else{
			$feeds = $feedManager->populate('name');
			echo 'Synchronisation complete...'."\n";
		}	
			
			echo count($feeds).' Flux a synchroniser...'."\n";
		foreach ($feeds as $feed) {
			echo date('H:i:s').' - Flux '.$feed->getName().' ('.$feed->getUrl().') parsage des flux...'."\n";
			$feed->parse();
			echo date('H:i:s').' - Flux '.$feed->getName().' ('.$feed->getUrl().') supression des vieux evenements...'."\n";
			if($maxEvents!=0) $feed->removeOldEvents($maxEvents);
			echo date('H:i:s').' - Flux '.$feed->getName().' ('.$feed->getUrl().') termin&eacute;'."\n";
			
		}
			echo date('H:i:s').' - Synchronisation terminée'."\n";
		if(isset($_['format'])) echo '</textarea>';



	break;

	case 'readAll':
		if($myUser==false) exit('Vous devez vous connecter pour cette action.');
		$feed = (isset($_['feed'])?array('feed'=>$_['feed']):null);
		$eventManager->change(array('unread'=>'0'),$feed);
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

			//Ajout des préférences et reglages
			$configurationManager->put('root',$_['root']);
			//$configurationManager->put('view',$_['view']);
			$configurationManager->put('articleView',$_['articleView']);
			$configurationManager->add('articleDisplayContent',$_['articleDisplayContent']);
			$configurationManager->add('articleDisplayAnonymous',$_['articleDisplayAnonymous']);

			$configurationManager->put('articlePerPages',$_['articlePerPages']);
			$configurationManager->put('articleDisplayLink',$_['articleDisplayLink']);
			$configurationManager->put('articleDisplayDate',$_['articleDisplayDate']);
			$configurationManager->put('articleDisplayAuthor',$_['articleDisplayAuthor']);
			$configurationManager->put('plugin_shaarli',(isset($_['plugin_shaarli']) && $_['plugin_shaarli']=='on'?1:0));
			$configurationManager->put('plugin_shaarli_link',$_['plugin_shaarli_link']);
			$configurationManager->put('synchronisationType',$_['synchronisationType']);
			$configurationManager->add('feedMaxEvents',$_['feedMaxEvents']);


	header('location: ./addFeed.php');
	break;

	case 'exportFeed':
				/*********************/
			/** Export **/
			/*********************/
			if(isset($_POST['exportButton'])){
				$feeds = $feedManager->populate('name');
				$folders = $folderManager->loadAll(array('parent'=>-1),'name');
				$xmlStream = '<?xml version="1.0" encoding="utf-8"?>
	<opml version="2.0">
		<head>
			<title>Leed export</title>
			<ownerName>Leed</ownerName>
			<ownerEmail>idleman@idleman.fr</ownerEmail>
			<dateCreated>'.date('D, d M Y H:i:s').'+0000</dateCreated>
		</head>
		<body>';

				$xmlStream .= Functions::recursiveExportOutline($folders);
				// foreach($folders as $folder){
				// 	$feeds = $folder->getFeeds();
				// 	$xmlStream .='<outline text="'.$folder->getName().'" title="'.$folder->getName().'" icon="">'."\n";
				// 		foreach($feeds as $feed){
				// 			$xmlStream .= '				<outline xmlUrl="'.$feed->getUrl().'" htmlUrl="'.$feed->getWebsite().'" text="'.$feed->getDescription().'" title="'.$feed->getName().'" description="'.$feed->getDescription().'" />'."\n";
				// 		}
				// 	$xmlStream .= '			</outline>';
				// }
				
		$xmlStream .= '</body>
	</opml>';


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
		echo '<link rel="stylesheet" href="css/style.css"><form action="action.php?action=importFeed" method="POST" enctype="multipart/form-data"><h2>Importer les flux au format opml</h2>
					<p>Fichier OPML : <input name="newImport" type="file"/> <button name="importButton">Importer</button></p>
					<p>Nb : L\'importation peux prendre un certain temps, laissez votre navigateur tourner et allez vous prendre un caf&eacute; :).</p></form>
				
			';
	break;

	case 'changeFolderState':
		if($myUser==false) exit('Vous devez vous connecter pour cette action.');
		$folderManager->change(array('isopen'=>$_['isopen']),array('id'=>$_['id']));
	break;

	case 'importFeed':
				if (ob_get_level() == 0) ob_start();
				ignore_user_abort(true);
			
				echo '<link rel="stylesheet" href="css/style.css"><ul style="font-family:Verdana;">';
				echo str_pad('',4096)."\n";ob_flush();flush();
				

				if($myUser==false) exit('Vous devez vous connecter pour cette action.');
				if(isset($_POST['importButton'])){
			
				echo '<li>Lecture du fichier OPML...</li>';
				echo str_pad('',4096)."\n";ob_flush();flush();
				$xml = simplexml_load_file($_FILES['newImport']['tmp_name']);
				$report = 'Import de flux depart : '.date('d/m/Y H:i:s')."\n";
				echo '<li>Parsage recursif du fichier OPML...</li>';
				echo str_pad('',4096)."\n";ob_flush();flush();
				$report .= Functions::recursiveImportXmlOutline($xml->body->outline,1);
				$report .= 'Import de flux fin : '.date('d/m/Y H:i:s')."\n";
				echo '<li>Création des logs d\'imports....</li>';
				echo str_pad('',4096)."\n";ob_flush();flush();
				file_put_contents('./logs/Import du '.date('d-m-Y').'.log', $report ,FILE_APPEND);
				echo '<li>Import des flux terminé.</li>';
				echo str_pad('',4096)."\n";ob_flush();flush();

				echo '</ul>';
				echo str_pad('',4096)."\n";ob_flush();flush();

				ob_end_flush();
				//header('location: ./addFeed.php');
			}
	break;

	
	case 'addFeed':
			if($myUser==false) exit('Vous devez vous connecter pour cette action.');
			if(isset($_['newUrl'])){
				$newFeed = new Feed();
				$newFeed->setUrl($_['newUrl']);
				$newFeed->getInfos();
				$newFeed->setFolder((isset($_['newUrlCategory'])?$_['newUrlCategory']:1));
				$newFeed->save();
				$newFeed->parse();
				header('location: ./addFeed.php');
			}
	break;

	case 'changeFeedFolder':
		if($myUser==false) exit('Vous devez vous connecter pour cette action.');
		if(isset($_['feed'])){
			$feedManager->change(array('folder'=>$_['folder']),array('id'=>$_['feed']));
		}
		header('location: ./addFeed.php');
	break;

	case 'removeFeed':
		if($myUser==false) exit('Vous devez vous connecter pour cette action.');
		if(isset($_GET['id'])){
			$feedManager->delete(array('id'=>$_['id']));
			$eventManager->delete(array('feed'=>$_['id']));
		}
		header('location: ./addFeed.php');
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
		header('location: ./addFeed.php');
	break;


	case 'renameFolder':
		if($myUser==false) exit('Vous devez vous connecter pour cette action.');
		if(isset($_['id'])){
			$folderManager->change(array('name'=>$_['name']),array('id'=>$_['id']));
		}
	break;

	case 'removeFolder':
		if($myUser==false) exit('Vous devez vous connecter pour cette action.');
		if(isset($_['id'])){
			$eventManager->query('DELETE FROM event WHERE event.feed in (SELECT feed.id FROM feed WHERE feed.folder ='.$_['id'].') ;');
			$feedManager->delete(array('folder'=>$_['id']));
			$folderManager->delete(array('id'=>$_['id']));
		}
		header('location: ./addFeed.php');
	break;

	case 'readContent':
		$event = $eventManager->load(array('id'=>$_['id']));
		if($myUser!=false) $eventManager->change(array('unread'=>'0'),array('id'=>$_['id']));
	break;

	case 'unreadContent':
		$event = $eventManager->load(array('id'=>$_['id']));
		if($myUser!=false) $eventManager->change(array('unread'=>'1'),array('id'=>$_['id']));
	break;

	case 'addFavorite':
		$eventManager->change(array('favorite'=>'1'),array('id'=>$_['id']));
	break;

	case 'removeFavorite':
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