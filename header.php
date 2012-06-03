<?php require_once('common.php'); ?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>Leed V1.0</title>
	<meta name="description" content="">
	<meta name="author" content="">
	<meta name="viewport" content="width=device-width">
	<link rel="stylesheet" href="css/style.css">
</head>
<body>

	<div id="header-container">

		<header class="wrapper clearfix">

			<h1 class="logo" id="title"><a href="./index.php">L<i>eed</i></a></h1>
			
				<div class="loginBloc">
			<?php if(!$myUser){ ?>
			<form action="action.php?action=login" method="POST">
					<input type="text" class="miniInput left" name="login" placeholder="Identifiant"/> <input type="password" class="miniInput left" name="password" placeholder="Mot de passe"/> <button class="left">GO!!</button>
			</form>
			<?php }else{ ?>
				<span>Identifi&eacute; avec <span><?php echo $myUser->getLogin(); ?></span></span><button onclick="window.location='action.php?action=logout'">D&eacute;connexion</button>
			<?php } ?>
			</div>
			
			<nav>
				<ul>
					<li><a href="index.php">Accueil</a></li>
					<li><a href="index.php?action=favorites">Favoris</a></li>
					<li><a href="addFeed.php">Gestion</a></li>
				</ul>
			</nav>
		</header>
	</div>
	<div id="main-container">


			