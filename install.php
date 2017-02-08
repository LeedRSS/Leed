<?php

/*
 @nom: install
 @auteur: Idleman (idleman@idleman.fr)
 @description:  Page d'installation du script (a supprimer après installation)
 */

require_once('Functions.class.php');
require_once('Install.class.php');
require_once('i18n.php');
global $i18n;
$install = new Install();

/* Prend le choix de langue de l'utilisateur, soit :
 * - lorsqu'il vient de changer la langue du sélecteur ($lang)
 * - lorsqu'il vient de lancer l'installeur ($install_changeLngLeed)
 */
$lang = '';
if (isset($_GET['lang'])) $lang = $_GET['lang'];
elseif (isset($_POST['install_changeLngLeed'])) $lang = $_POST['install_changeLngLeed'];

$installDirectory = dirname(__FILE__).'/install';
if (empty($lang))
    $currentLanguage = i18n_init(Functions::getBrowserLanguages(),$installDirectory);
else
    $currentLanguage = i18n_init($lang,$installDirectory);

$languageList = $i18n->languages;

if (file_exists('constant.php')) {
    die(_t('ALREADY_INSTALLED'));
}

/* Nombres de thèmes disponibles
 * 0 - Pas possible, car il y aura au moins Marigolds
 * 1 - Indique le thème (Marigolds), mais ne permet pas la modification
 * 2 - Indique un thème et permet la sélection. Marigolds est mis en premier. 
 */
define('DEFAULT_TEMPLATE', 'marigolds');
$templates = scandir('templates');
if (!in_array(DEFAULT_TEMPLATE, $templates)) die('Missing default template : '.DEFAULT_TEMPLATE);
$templates = array_diff($templates, array(DEFAULT_TEMPLATE, '.', '..')); // Répertoires non voulus sous Linux
sort($templates);
$templates = array_merge(array(DEFAULT_TEMPLATE), $templates); // le thème par défaut en premier

// Cookie de la session
$cookiedir = '';
if(dirname($_SERVER['SCRIPT_NAME'])!='/') $cookiedir=dirname($_SERVER["SCRIPT_NAME"]).'/';
session_set_cookie_params(0, $cookiedir);
session_start();

// Protection des variables
$_ = array_merge($_GET, $_POST);

$install->overrideDefaultValues($_);
$install->launch(isset($_['installButton']));

?>
<!doctype html>
<html lang="<?php echo $currentLanguage;?>">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title><?php echo _t('INSTALL_TITLE') ?></title>
    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" href="templates/marigolds/css/style.css">
    <style>
        code {
            color:#000;
            font-size: 1em;
        }
        .install h1 {
            margin-bottom: 1.3em;
        }
        .install h2 {
            margin-bottom: 0.1em;
            font-size: 1.5em;
        }
        .install ul {
            margin: 0;
            padding: 0;
        }
        .install li {
            list-style: none outside none;
        }
        .install span {
            display: inline-block;
            width: 8em;
            padding-right: 1em;
        }
        button#installButton {
            margin-top: 1em;
            font-size: 2em;
        }
        .message {
            color: #ffffff;
            margin-bottom: 2em;
        }
        .message li {
            border:1px solid #212121
        }
        .messageError {
            background-color: #F16529;
        }
        .messageSuccess {
            background-color: #008000;
        }
    </style>
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
    <?php
    if ($install->getFinished()){
        echo '<div id="main-container">
                <div id="main" class="wrapper clearfix">
                    <div id="menuBar"></div>
                        <h1>'._t('INSTALL_TITLE_END').'</h1>
                        <span>'._t('INSTALL_END').'</span>
                        <hr>
                        <button id="installButton" name="installButton" onclick="document.location.href=\'settings.php#preferenceBloc\'">'._t('INSTALL_BTN_END').'</button>
              ';
        // écriture des balises de fin et ne pas faire la suite
        echo '</div>
            <div id="footer-container">
                <footer class="wrapper">
                    <p>Leed "Light Feed" by <a target="_blank" href="http://blog.idleman.fr">Idleman</a></p>
                </footer>
            </div>
            </body>
            </html>';
        exit();
    }


    ?>
    <div id="main-container">
        <div id="main" class="wrapper clearfix">
        <div id="menuBar">
        <aside>
            <h3 class="left"><?php echo _t('INSTALL_PRE_REQUIS') ?></h3>
            <ul class="clear" style="margin:0">
            <?php
                foreach($install->logs as $type => $messages){
                    if(empty($messages)) {
                        continue;
                    }
                    $class = 'message ';
                    $class .= $type === 'errors' ? 'messageError':'messageSuccess';
                    $label = $type === 'errors' ? _t('ERROR') : _t('SUCCESS');
                    echo "<li class='$class'>$label&nbsp;:<ul>";

                    foreach ($messages as $message){
                        echo "<li>$message</li>";
                    }
                    echo "</ul></li>";
                }
            ?>
            </ul>
        </aside>
    </div>
    <form action="install.php" method="POST" class="install">
        <h1><?php echo _t('INSTALL_TITLE') ?></h1>
        <h2><?php echo _t('INSTALL_TAB_GENERAL') ?></h2>
        <ul>
            <li>
                <span><?php echo _t('INSTALL_LANGUAGE') ?></span>
                <select name="install_changeLngLeed" onchange="window.location.href='install.php?lang='+this[this.selectedIndex].value">
                <?php
                    foreach($languageList as $lang){
                        $sel = $lang==$currentLanguage?'selected=selected':'';
                        echo "<option $sel value='$lang'>$lang</option>";
                    }
                ?>
                </select>
            </li>
            <li>
                <span><?php echo _t('INSTALL_TEMPLATE') ?></span>
                <?php
                    $disabled = count($templates)<2 ? "disabled" : "";
                    echo "<select name='template' $disabled>\n";
                    foreach($templates as $name){
                        echo "<option value='$name'>$name</option>";
                    }
                ?>
                </select>
            </li>
            <li>
                <span><?php echo _t('PROJECT_ROOT') ?></span>
                <input type="text" name="root" value="<?php echo $install->getDefaultRoot(); ?>">
            </li>
        </ul>
        <h2><?php echo _t('INSTALL_TAB_BDD') ?></h2>
        <ul>
            <li>
                <span><?php echo _t('INSTALL_HOST') ?></span>
                <input type="text" name="mysqlHost" value="<?php echo $install->options['db']['mysqlHost']; ?>" placeholder="<?php echo _t('INSTALL_COMMENT_HOST') ?>">
            </li>
            <li>
                <span><?php echo _t('LOGIN') ?></span>
                <input type="text" name="mysqlLogin" value="<?php echo $install->options['db']['mysqlLogin']; ?>">
            </li>
            <li>
                <span><?php echo _t('PASSWORD') ?></span>
                <input type="text" autocomplete="off" name="mysqlMdp" value="<?php echo $install->options['db']['mysqlMdp']; ?>" placeholder="<?php echo _t('INSTALL_DISPLAY_CLEAR') ?>">
            </li>
            <li>
                <span><?php echo _t('INSTALL_BDD') ?></span>
                <input type="text" name="mysqlBase" value="<?php echo $install->options['db']['mysqlBase']; ?>" placeholder="<?php echo _t('INSTALL_COMMENT_BDD') ?>">
            </li>
            <li>
                <span><?php echo _t('INSTALL_PREFIX_TABLE') ?></span>
                <input type="text" name="mysqlPrefix" value="<?php echo $install->options['db']['mysqlPrefix']; ?>">
            </li>
        </ul>
        <h2><?php echo _t('INSTALL_TAB_ADMIN') ?></h2>
        <ul>
            <li>
                <span><?php echo _t('LOGIN') ?></span>
                <input type="text" name="login" value="<?php echo $install->options['user']['login']; ?>" placeholder="<?php echo _t('LOGIN') ?>">
            </li>
            <li>
                <span><?php echo _t('PASSWORD') ?></span>
                <input type="text" autocomplete="off" name="password" value="<?php echo $install->options['user']['password']; ?>" placeholder="<?php echo _t('INSTALL_DISPLAY_CLEAR') ?>">
            </li>
        </ul>
        <button id="installButton" name="installButton"><?php echo _t('INSTALL_BTN') ?></button>
    </form>
</div>
<div id="footer-container">
    <footer class="wrapper">
        <p>Leed "Light Feed" by <a target="_blank" href="http://blog.idleman.fr">Idleman</a></p>
    </footer>
</div>
</body>
</html>
