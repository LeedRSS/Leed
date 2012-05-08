<?php require_once('header.php'); ?>
<?php
//$feeds = $feedManager->populate('name');
$folders = $folderManager->populate('name');
$currentFeed = false;

$shareOption = false;
if($configurationManager->get('plugin_shaarli')=='1'){
	$shareOption = $configurationManager->get('plugin_shaarli_link');
}


if(isset($_GET['action'])){
	switch($_GET['action']){
		case 'readFeed':
		$currentFeed = $feedManager->getById($_GET['feed']);
		break;

	}
}

?>
		<div id="main" class="wrapper clearfix">
			<aside>
				<h3 class="left">Flux</h3> <button style="margin: 20px 10px;" onclick="if(confirm('Tout marquer comme lu pour tous les flux?'))window.location='action.php?action=readAll'">Tout marquer comme lu</button>
				
				<ul class="clear">
					<?php foreach($folders as $folder){  
						$feeds = $folder->getFeeds();
						?>
					<li><h1 class="folder" onclick="toggleFolder(this,<?php echo $folder->getId(); ?>);"><?php echo $folder->getName().' ('.count($feeds).')'; ?></h1>
						<ul <?php if(!$folder->getIsopen()){ ?>style="display:none;"<?php } ?>>
							<?php if (count($feeds)!=0 ) {foreach($feeds as $feed){ ?>
								<li><a href="index.php?action=readFeed&feed=<?php echo $feed->getId();?>" alt="<?php echo $feed->getUrl(); ?>" title="<?php echo $feed->getUrl(); ?>"><?php echo $feed->getName(); ?> </a><?php $unread = $feed->countUnreadEvents(); if($unread!=0){ ?>  <button style="margin-left:10px;" onclick="if(confirm('Tout marquer comme lu pour ce flux?'))window.location='action.php?action=readAll&feed=<?php echo $feed->getId(); ?>'"><span alt="marquer comme lu" title="marquer comme lu"><?php echo $unread; ?></span></button><?php } ?> </li>
							<?php }} ?>
						</ul>
					</li>
					<?php } ?>
				</ul>

			</aside>
			<?php 

				$articleView = $configurationManager->get('articleView');
				$articlePerPages = $configurationManager->get('articlePerPages');
				$articleDisplayLink = $configurationManager->get('articleDisplayLink');
				$articleDisplayDate = $configurationManager->get('articleDisplayDate');
				$articleDisplayAuthor = $configurationManager->get('articleDisplayAuthor');
				
				if($currentFeed !=false){

				$numberOfFeed = $eventManager->rowCount(array('feed'=>$currentFeed->getId()));
				$page = (isset($_['page'])?$_['page']:1);
				$pages = round($numberOfFeed/$articlePerPages); 
				$startArticle = ($page-1)*$articlePerPages;
				
				$events = $currentFeed->getEvents($startArticle,$articlePerPages,'id');



			 ?>

			<article>
				<header>
					<h1><a target="_blank" href="<?php echo $currentFeed->getWebSite(); ?>"><?php echo $currentFeed->getName(); ?></a></h1>
					<p><?php echo $currentFeed->getDescription(); ?></p>
				
				</header>
				<?php  

				foreach($events as $event){
				 ?>
				<section <?php if(!$event->getUnread()){ ?>class="eventRead"<?php } ?> >
					<h2><a onclick="$(this).parent().parent().addClass('eventRead');" target="_blank" href="action.php?action=readContent&id=<?php echo $event->getId(); ?>" alt="Voir l'article sur le blog" title="Voir l'article sur le blog"><?php echo $event->getTitle(); ?></a></h2>
					<h3><?php if ($articleDisplayAuthor){ ?>Par <?php echo $event->getCreator(); } if ($articleDisplayLink){ ?> le <?php echo $event->getPubDate(); } if ($articleDisplayLink){ ?>- <a href="<?php echo $event->getGuid(); ?>" traget="_blank">Lien direct vers l'article</a><?php } if($shareOption!=false){ ?> <button  alt="partager sur shaarli" title="partager sur shaarli" onclick="window.location.href='<?php echo $shareOption.'/index.php?post='.rawurlencode($event->getGuid()).'&title='.$event->getTitle().'&source=bookmarklet' ?>'">Shaare</button><?php } ?> 
					</h3>
					<p><?php if ($articleView=='partial'){echo $event->getDescription();}else{echo $event->getContent();} ?></p>
				</section>
				<?php } ?>

				<p>Page <?php echo $page; ?>/<?php echo $pages; ?> : <?php for($i=1;$i<$pages+1;$i++){ ?> <a href="index.php?action=readFeed&feed=<?php echo $currentFeed->getId(); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a> | <?php } ?> </p>
			</article>
			<?php }else{ 
			
				

				$unreadEvents = $eventManager->rowCount(array('unread'=>1));
				$page = (isset($_['page'])?$_['page']:1);
				$pages = round($unreadEvents/$articlePerPages); 
				$startArticle = ($page-1)*$articlePerPages;
				$events = $eventManager->loadAll(array('unread'=>1),'pubDate',$startArticle.','.$articlePerPages);

				?>

				<article>
				<header>
					<h1>Non lu (<?php echo $unreadEvents; ?>)</h1>
				</header>

			<?php
				
				foreach($events as $event){
			 ?>
				<section <?php if(!$event->getUnread()){ ?>class="eventRead"<?php } ?> >
					<h2><a onclick="$(this).parent().parent().addClass('eventRead');" target="_blank" href="action.php?action=readContent&id=<?php echo $event->getId(); ?>" alt="Voir l'article sur le blog" title="Voir l'article sur le blog"><?php echo $event->getTitle(); ?></a></h2>
					<h3><?php if ($articleDisplayAuthor){ ?>Par <?php echo $event->getCreator(); } if ($articleDisplayLink){ ?> le <?php echo $event->getPubDate(); } if ($articleDisplayLink){ ?>- <a href="<?php echo $event->getGuid(); ?>" traget="_blank">Lien direct vers l'article</a><?php } if($shareOption!=false){ ?> <button alt="partager sur shaarli" title="partager sur shaarli"  onclick="window.location.href='<?php echo $shareOption.'/index.php?post='.rawurlencode($event->getGuid()).'&title='.$event->getTitle().'&source=bookmarklet' ?>'">Shaare</button><?php } ?>
					</h3>
					<p><?php if ($articleView=='partial'){echo $event->getDescription();}else{echo $event->getContent();} ?></p>

				</section>

			<?php } ?>
			<p>Page <?php echo $page; ?>/<?php echo $pages; ?> : <?php for($i=1;$i<$pages+1;$i++){ ?> <a href="index.php?page=<?php echo $i; ?>"><?php echo $i; ?></a> | <?php } ?> </p>
			</article><?php } ?>
			


			
			
		</div> <!-- #main -->

<?php require_once('footer.php'); ?>
