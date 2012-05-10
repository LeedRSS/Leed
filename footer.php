
	</div> <!-- #main-container -->

	<div id="footer-container">
		

		<aside class="loginBloc">
			<?php if(!$myUser){ ?>
			<form action="action.php?action=login" method="POST">
					<input type="text" class="miniInput left" name="login" placeholder="Identifiant"/> <input type="password" class="miniInput left" name="password" placeholder="Mot de passe"/> <button class="left">GO!!</button>
			</form>
			<?php }else{ ?>
				<span>Identifi&eacute; avec <span><?php echo $myUser->getLogin(); ?></span></span><button onclick="window.location='action.php?action=logout'">D&eacute;connexion</button>
			<?php } ?>
			</aside>

		<footer class="wrapper">
			<p>Leed "Light Feed" by <a target="_blank" href="http://blog.idleman.fr">Idleman</a> | <a href="about.php">A propos</a></p>
		</footer>

		<div class="clear"></div>
	</div>


<script src="js/libs/jquery-1.7.2.min.js"></script>
<script src="js/script.js"></script>

</body>
</html>
