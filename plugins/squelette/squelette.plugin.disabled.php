<?php
/*
@name Squelette (nom du plugin affiché)
@author Nom de l'auteur <mail@auteur.fr>
@link http://site.de.l.auteur.fr
@licence Ma Licence
@version 1.0.0
@description Le plugin Squelette est un plugin d'exemple pour les créateurs de nouveaux plugins Leed, il ne fait rien de particulier
*/


function squelette_plugin_menu(&$myUser){
	/* Ajoutez un code qui s'executera après le menu des flux ex :
	
	echo '<aside class="squeletteMenu">
				
				<h3 class="left">A lire</h3>
					<ul class="clear">  							  								  							  							  								  	
					<li>
						<ul> 
							<li> 
								<img src="plugins/squelette/img/read_icon.png">
								
									<a href="action.php?action=squelette_action">'.Functions::truncate("Hourra pour Leed et vive les navets!!",30).'</a>
										  
								<button class="right" onclick="squelette_javascript()" style="margin-left:10px;">
									<span>Pouet</span>
								</button>
								</li>
						</ul>
					</li>
				</ul>
			</aside>';
		*/	
}

function squelette_plugin_action($_,$myUser){

	/* Ajoutez un code qui s'executera en tant qu'action ex : 
	
	if($_['action']=='squelette_action'){
		if($myUser==false) exit('Vous devez vous connecter pour cette action.');
		if($_['state']=='add'){
			$return = mysql_query('INSERT INTO '.MYSQL_PREFIX.'plugin_feaditlater (event)VALUES(\''.$_['id'].'\')');
		}else{
			$return = mysql_query('DELETE FROM '.MYSQL_PREFIX.'plugin_feaditlater WHERE event=\''.$_['id'].'\'');
		}
		if(!$return) echo mysql_error();
	}
	*/
}

//Ajout du css du squelette en en tête de leed
Plugin::addCss("/css/style.css"); 

//Ajout du javascript du squelette au bas de page de leed
Plugin::addJs("/js/main.js"); 
 
//Ajout de la fonction squelette_plugin_displayEvents au Hook situé après le menu des flux
Plugin::addHook("menu_post_folder_menu", "squelette_plugin_menu");  
//Ajout de la fonction squelette_plugin_action à la page action de leed qui contient tous les traitements qui n'ont pas besoin d'affichage (ex :supprimer un flux, faire un appel ajax etc...)
Plugin::addHook("action_post_case", "squelette_plugin_action");  
?>