<?php

/*
 @nom: install
 @auteur: Idleman (idleman@idleman.fr)
 @description:  Page d'installation du script (a supprimer après installation)
 */

require_once('Functions.class.php');
require_once('i18n.php');
global $i18n;
$install_terminee=false;

if (isset($_GET['lang']))
    $currentLanguage = i18n_init($_GET['lang']);
else
    $currentLanguage = i18n_init(Functions::getBrowserLanguages());

$languageList = $i18n->languages;

if (file_exists('constant.php')) {
    die(_t('ALREADY_INSTALLED'));
}

// Cookie de la session
$cookiedir = '';
if(dirname($_SERVER['SCRIPT_NAME'])!='/') $cookiedir=dirname($_SERVER["SCRIPT_NAME"]).'/';
session_set_cookie_params(0, $cookiedir);
session_start();

// Protection des variables
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

// Valeurs par défaut, remplacées si une autre valeur est saisie.
foreach (array('login','mysqlBase','mysqlHost','mysqlLogin','mysqlMdp','mysqlPrefix','password','root') as $var) {
    /* Initalise les variables avec le contenu des champs
     * pour rappeler les valeurs déjà saisies. */
    if (!empty($_[$var]))
        $$var = $_[$var];
    else
        $$var = '';
}
if (empty($root)) {
    // Ne peut être vide, alors on met la valeur par défaut
    $root = str_replace(
        basename(__FILE__),
        '',
        'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']
    );
}
if (!isset($_['mysqlPrefix'])) {
    // Le formulaire n'étant pas soumis, on met cette valeur par défaut.
    $mysqlPrefix = 'leed_';
}

$lib_errors = _t('ERROR');
$lib_success = _t('SUCCESS');

if(isset($_['installButton'])){
    if (empty($_['password']) || empty($_['login'])) {
        $test[$lib_errors][] = _t('INSTALL_ERROR_USERPWD');
    }
    if (!Functions::testDb(
        $_['mysqlHost'], $_['mysqlLogin'], $_['mysqlMdp'], $_['mysqlBase']
    )) {
        $test[$lib_errors][] = _t('INSTALL_ERROR_CONNEXION');
    } else {
        $test[$lib_success][] = _t('INSTALL_INFO_CONNEXION');
    }
}
if(!is_writable('./')){
    $test[$lib_errors][]=_t('INSTALL_ERROR_RIGHT', array(str_replace(basename(__FILE__),'',__FILE__)));
}else{
    $test[$lib_success][]=_t('INSTALL_INFO_RIGHT');
}
if (!@function_exists('mysql_connect')){
    $test[$lib_errors][] = _t('INSTALL_ERROR_MYSQLCONNECT');
}else{
    $test[$lib_success][] = _t('INSTALL_INFO_MYSQLCONNECT');
}
if (!@function_exists('file_get_contents')){
    $test[$lib_errors][] =  _t('INSTALL_ERROR_FILEGET');
}else{
    $test[$lib_success][] = _t('INSTALL_INFO_FILEGET');
}
if (!@function_exists('file_put_contents')){
    $test[$lib_errors][] = _t('INSTALL_ERROR_FILEPUT');
}else{
    $test[$lib_success][] = _t('INSTALL_INFO_FILEPUT');
}
if (@version_compare(PHP_VERSION, '5.1.0') <= 0){
    $test[$lib_errors][] = _t('INSTALL_ERROR_PHPV', array(PHP_VERSION));
}else{
    $test[$lib_success][] = _t('INSTALL_INFO_PHPV', array(PHP_VERSION));
}
if(ini_get('safe_mode') && ini_get('max_execution_time')!=0){
    $test[$lib_errors][] = _t('INSTALL_ERROR_SAFEMODE');
}else{
    $test[$lib_success][] = _t('INSTALL_INFO_SAFEMODE');
}

if (isset($_['installButton']) && empty($test[$lib_errors])) { // Pas d'erreur, l'installation peut se faire.
    $constant = "<?php
    define('VERSION_NUMBER','1.6');
    define('VERSION_NAME','Stable');

    //Host de Mysql, le plus souvent localhost ou 127.0.0.1
    define('MYSQL_HOST','{$mysqlHost}');
    //Identifiant MySQL
    define('MYSQL_LOGIN','{$mysqlLogin}');
    //mot de passe MySQL
    define('MYSQL_MDP','{$mysqlMdp}');
    //Nom de la base MySQL ou se trouvera leed
    define('MYSQL_BDD','{$mysqlBase}');
    //Prefix des noms des tables leed pour les bases de données uniques
    define('MYSQL_PREFIX','{$mysqlPrefix}');
    //Theme graphique
    define('DEFAULT_THEME','marigolds');
    //Nombre de pages affichées dans la barre de pagination
    define('PAGINATION_SCALE',5);
    //Nombre de flux mis à jour lors de la synchronisation graduée
    define('SYNC_GRAD_COUNT',10);
    //Langue utilisée
    define('LANGUAGE','".$_POST['install_changeLngLeed']."');
?>";

    file_put_contents('constant.php', $constant);
    if (!is_readable('constant.php'))
        die('"constant.php" not found!');

    require_once('constant.php');
    require_once('MysqlEntity.class.php');
    class_exists('Update') or require_once('Update.class.php');
    Update::ExecutePatch(true);
    require_once('Feed.class.php');
    require_once('Event.class.php');

    require_once('User.class.php');
    require_once('Folder.class.php');
    require_once('Configuration.class.php');

    $cryptographicSalt = User::generateSalt();
    $synchronisationCode = substr(sha1(rand(0,30).time().rand(0,30)),0,10);
    $root = (substr($_['root'], strlen($_['root'])-1)=='/'?$_['root']:$_['root'].'/');

    // DOSSIERS À CONSERVER TELS QUELS, SI DÉJÀ EXISTANTS
    $feedManager = new Feed(); $feedManager->create();
    $eventManager = new Event(); $eventManager->create();

    // COMPTE ADMINISTRATEUR, RÀZ SI NÉCESSAIRE
    $userManager = new User();
    if ($userManager->tableExists()) {
        // Suppose qu'il n'y a qu'un seul utilisateur
        $userManager->truncate();
    }
    $userManager->create();
    $admin = new User();
    $admin->setLogin($_['login']);
    $admin->setPassword($_['password'],$cryptographicSalt);
    $admin->save();
    $_SESSION['currentUser'] = serialize($admin);

    // DOSSIERS DE FLUX, RECRÉE LE DOSSIER GÉNÉRAL SI NÉCESSAIRE
    $folderManager = new Folder();
    $folderManager->create();
    if ($folderManager->rowCount()==0) {
        //Création du dossier général
        $folder = new Folder();
        $folder->setName(_t('GENERAL_FOLDER'));
        $folder->setParent(-1);
        $folder->setIsopen(1);
        $folder->save();
    }

    // REMET À ZÉRO LA CONFIGURATION
    $configurationManager = new Configuration();
    if ($configurationManager->tableExists()) {
        $configurationManager->truncate();
    }
    $configurationManager->create();
    $configurationManager->add('root',$root);
    $configurationManager->add('articleView','partial');
    $configurationManager->add('articleDisplayContent','1');
    $configurationManager->add('articleDisplayAnonymous','0');
    $configurationManager->add('articlePerPages','5');
    $configurationManager->add('articleDisplayLink','1');
    $configurationManager->add('articleDisplayDate','1');
    $configurationManager->add('articleDisplayAuthor','1');
    $configurationManager->add('articleDisplayHomeSort','1');
    $configurationManager->add('articleDisplayFolderSort','1');
    $configurationManager->add('displayOnlyUnreadFeedFolder','false');
    $configurationManager->add('optionFeedIsVerbose',1);
    $configurationManager->add('synchronisationType','auto');
    $configurationManager->add('feedMaxEvents','50');
    $configurationManager->add('synchronisationCode',$synchronisationCode);
    $configurationManager->add('synchronisationEnableCache','1');
    $configurationManager->add('synchronisationForceFeed','0');
    $configurationManager->add('cryptographicSalt', $cryptographicSalt);

    $install_terminee=true;
} /* Ci-dessous, on y va si :
- la page est simplement affichée, sans avoir été validée
- le formulaire est soumis, mais l'installation ne peut se faire
*/
?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
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
    if ($install_terminee){
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
                foreach($test as $type=>$messages){
                    $class = 'message ';
                    $class .= $lib_errors==$type ? 'messageError':'messageSuccess';
                    echo "<li class='$class'>$type&nbsp;:\n<ul>";
                    foreach ($messages as $message){
                        echo "<li>$message</li>\n";
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
                <span><?php echo _t('PROJECT_ROOT') ?></span>
                <input type="text" name="root" value="<?php echo $root; ?>">
            </li>
        </ul>
        <h2><?php echo _t('INSTALL_TAB_BDD') ?></h2>
        <ul>
            <li>
                <span><?php echo _t('INSTALL_HOST') ?></span>
                <input type="text" name="mysqlHost" value="<?php echo $mysqlHost; ?>" placeholder="<?php echo _t('INSTALL_COMMENT_HOST') ?>">
            </li>
            <li>
                <span><?php echo _t('LOGIN') ?></span>
                <input type="text" name="mysqlLogin" value="<?php echo $mysqlLogin; ?>">
            </li>
            <li>
                <span><?php echo _t('PASSWORD') ?></span>
                <input type="text" autocomplete="off" name="mysqlMdp" value="<?php echo $mysqlMdp; ?>" placeholder="<?php echo _t('INSTALL_DISPLAY_CLEAR') ?>">
            </li>
            <li>
                <span><?php echo _t('INSTALL_BDD') ?></span>
                <input type="text" name="mysqlBase" value="<?php echo $mysqlBase; ?>" placeholder="<?php echo _t('INSTALL_COMMENT_BDD') ?>">
            </li>
            <li>
                <span><?php echo _t('INSTALL_PREFIX_TABLE') ?></span>
                <input type="text" name="mysqlPrefix" value="<?php echo $mysqlPrefix; ?>">
            </li>
        </ul>
        <h2><?php echo _t('INSTALL_TAB_ADMIN') ?></h2>
        <ul>
            <li>
                <span><?php echo _t('LOGIN') ?></span>
                <input type="text" name="login" value="<?php echo $login; ?>" placeholder="<?php echo _t('LOGIN') ?>">
            </li>
            <li>
                <span><?php echo _t('PASSWORD') ?></span>
                <input type="text" autocomplete="off" name="password" value="<?php echo $password; ?>" placeholder="<?php echo _t('INSTALL_DISPLAY_CLEAR') ?>">
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
