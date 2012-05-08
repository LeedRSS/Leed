<?php

require_once("common.php");



//Execution du code en fonction de l'action
switch ($_['action']){

	case 'synchronize':
		$feeds = $feedManager->populate('name');
			echo '------------------------------------------------------------------'."\n";
			echo '-------------- Synchronisation du '.date('d/m/Y').' --------------'."\n";
			echo '------------------------------------------------------------------'."\n";
			echo count($feeds).' Flux a synchroniser...'."\n";
		foreach ($feeds as $feed) {
			$feed->parse();
			echo date('H:i:s').' - Flux '.$feed->getName().' : OK'."\n";;
		}
	break;

	case 'readAll':
		if($myUser==false) exit('Vous devez vous connecter pour cette action.');
		$feed = (isset($_['feed'])?array('feed'=>$_['feed']):null);
		$eventManager->change(array('unread'=>'0'),$feed);
		header('location: ./index.php');

	break;

	case 'updateConfiguration':
		if($myUser==false) exit('Vous devez vous connecter pour cette action.');

			//Ajout des préférences et reglages
			$configurationManager->put('root',$_['root']);
			//$configurationManager->put('view',$_['view']);
			$configurationManager->put('articleView',$_['articleView']);

			$configurationManager->put('articlePerPages',$_['articlePerPages']);
			$configurationManager->put('articleDisplayLink',$_['articleDisplayLink']);
			$configurationManager->put('articleDisplayDate',$_['articleDisplayDate']);
			$configurationManager->put('articleDisplayAuthor',$_['articleDisplayAuthor']);
	
		header('location: ./addFeed.php');
	break;

	case 'exportFeed':
				/*********************/
			/** Export **/
			/*********************/
			if(isset($_POST['exportButton'])){
				$feeds = $feedManager->populate('name');
				$folders = $folderManager->populate('name');
				$xmlStream = '<?xml version="1.0" encoding="utf-8"?>
	<opml version="2.0">
		<head>
			<title>Leed export</title>
			<ownerName>Leed</ownerName>
			<ownerEmail>idleman@idleman.fr</ownerEmail>
			<dateCreated>'.date('D, d M Y H:i:s').'+0000</dateCreated>
		</head>
		<body>';

				foreach($folders as $folder){
					$feeds = $folder->getFeeds();
					$xmlStream .='<outline text="'.$folder->getName().'" title="'.$folder->getName().'" icon="">'."\n";
						foreach($feeds as $feed){
							$xmlStream .= '				<outline xmlUrl="'.$feed->getUrl().'" htmlUrl="'.$feed->getWebsite().'" text="'.$feed->getDescription().'" title="'.$feed->getName().'" description="'.$feed->getDescription().'" />'."\n";
						}
					$xmlStream .= '			</outline>';
				}
				
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
	
	case 'changeFolderState':
		if($myUser==false) exit('Vous devez vous connecter pour cette action.');
		$folderManager->change(array('isopen'=>$_['isopen']),array('id'=>$_['id']));
	break;

	case 'importFeed':

				if($myUser==false) exit('Vous devez vous connecter pour cette action.');
				if(isset($_POST['importButton'])){
				set_time_limit (360);
				$xml = simplexml_load_file($_FILES['newImport']['tmp_name']);
				$level = $xml->xpath('body//outline');
				foreach($level as $item){
					$level2 = $item->outline;
					foreach($level2 as $item2){
						$newFeed = new Feed();
						$newFeed->setName($item2[0]['title']);

						$folder = $folderManager->load(array('name'=>$item['title']));
						$folder = (!$folder?new Folder():$folder);
						$folder->setName($item['title']);
						$folder->setParent(-1);
						$folder->setIsopen(0);
						$folder->save();
						$newFeed->setFolder($folder->getId());

						$newFeed->setUrl($item2[0]['xmlUrl']);
						$newFeed->setDescription($item2[0]['description']);
						$newFeed->setWebsite($item2[0]['htmlUrl']);
						$newFeed->save();
						$newFeed->parse();
					}
				}
				header('location: ./addFeed.php');
			}
	break;

	
	case 'addFeed':
			if($myUser==false) exit('Vous devez vous connecter pour cette action.');
			if(isset($_['newUrl'])){
				$newFeed = new Feed();
				$newFeed->setUrl($_['newUrl']);
				$newFeed->getInfos();
				$newFeed->setFolder(1);
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
			$feedManager->delete(array('id'=>$_GET['id']));
			$eventManager->delete(array('feed'=>$_GET['id']));
		}
		header('location: ./addFeed.php');
	break;

	case 'addFolder':
		if($myUser==false) exit('Vous devez vous connecter pour cette action.');
		if(isset($_['newFolder'])){
			$folder = new Folder();
			$folder->setParent(-1);
			$folder->setIsopen(0);
			$folder->setName($_['newFolder']);
			$folder->save();
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

			$eventManager->query('DELETE * FROM event INNER JOIN folder ON ( feed.folder = folder.id ) INNER JOIN event ON ( feed.id = event.feed ) WHERE folder.id = '.$_['id'].' ;');
			$feedManager->delete(array('folder'=>$_['id']));
			$folderManager->delete(array('id'=>$_['id']));

		}
		header('location: ./addFeed.php');
	break;

	case 'readContent':
		$event = $eventManager->load(array('id'=>$_GET['id']));
		$event->setUnread(0);
		$event->save();
		header('location: '.$event->getGuid());
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