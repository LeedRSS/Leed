<?php
/*
@name ThemeSwitcher
@author Simounet <contact@simounet.net>
@link http://www.simounet.net
@licence CC BY-SA
@version 1.0.0
@description Le plugin ThemeSwitcher permet de changer de thème via la page de gestion.
*/

function themeswitcher_plugin_AddLink_and_Save(){
	echo '<li class="pointer" onclick="$(\'#main section\').hide();$(\'#themeSwitcher\').fadeToggle(200);">Changer de thème</li>';
	if(isset($_POST['themeSelected'])){
		themeswitcher_plugin_change();
	}
}

function themeswitcher_plugin_AddForm(){
	$themes = getThemes();
	echo '
	<section id="themeSwitcher">
		<form action="settings.php" method="post">
			<h2>Changer de thème :</h2>
			<select name="themeSelected" id="themeSelected">';
				foreach($themes as $theme){
					echo '<option value="'.$theme.'">'.$theme.'</option>';
				}
			echo '
			</select>
			<button type="submit">Enregistrer</button>
		</form>
	</section>
';
}

function themeswitcher_plugin_change(){
    $fileName = 'constant.php';
    $searchfor = 'marigolds';
    $file = file_get_contents($fileName);
    $result = preg_replace("/'DEFAULT_THEME',(.?)'(.*)'/", "'DEFAULT_THEME','".$_POST['themeSelected']."'", $file);
    $put = file_put_contents($fileName, $result);
    if(!$put){
        echo "Vous ne devez pas avoir les droits d'écriture sur le fichier constant.php. Effectuez un <code>chmod 664 constant.php</code>";
    }
}

/* Tools */
function getThemes(){
	$themesDir = 'templates/';
	$dirs = scandir($themesDir);
	foreach($dirs as $dir){
	    if(is_dir($themesDir.$dir) && !in_array($dir,array(".","..")) ){
	        $themes[]=$dir;
	    }
	}
	
	sort($themes);
	return $themes;
}

Plugin::addHook("setting_post_link", "themeswitcher_plugin_AddLink_and_Save");
Plugin::addHook("setting_post_section", "themeswitcher_plugin_AddForm");

?>
