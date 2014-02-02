<?php

/*
 @nom: install
 @auteur: Idleman (idleman@idleman.fr)
 @description:  Page d'installation du script (a supprimer après installation)
 */

if (file_exists('constant.php')) {
    die('Leed est déjà configuré. Supprimez ou renommez le fichier de configuration.');
}

$cookiedir = '';
if(dirname($_SERVER['SCRIPT_NAME'])!='/') $cookiedir=dirname($_SERVER["SCRIPT_NAME"]).'/';
session_set_cookie_params(0, $cookiedir);
session_start();
require_once('Functions.class.php');
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

?>


<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Installation</title>
    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" href="templates/marigolds/css/style.css">
    <style>
        code {
            color:#000;
            font-size: 1em;
        }
        td {
            padding-right: 1em;
        }
        th {
            text-align: left;
            font-size: 1.5em;
            padding-top: 1em;
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
    <div id="main-container">
        <div id="main" class="wrapper clearfix">
<?php
if(isset($_['installButton'])){

    if (empty($_['password']) || empty($_['login'])) {
        echo "<p>Par sécurité, il est nécessaire de fournir un nom d'utilisateur et un mot de passe.</p>";
        die();
    }

    if (!Functions::testDb(
        $_['mysqlHost'], $_['mysqlLogin'], $_['mysqlMdp'], $_['mysqlBase']
    )) {
        ///@TODO: faire un retour plus intelligible + tests dans le common.php
        echo "<p>Connexion à la base de donnnées impossible :</p>";
        echo "<ul>\n";
        echo "<li>host: {$_['mysqlHost']}\n";
        echo "<li>login: {$_['mysqlLogin']}\n";
        echo "<li>password: {$_['mysqlMdp']}\n";
        echo "<li>database: {$_['mysqlBase']}\n";
        echo "</ul><p><a href=''>Relancer l'installation</a></p>\n";
        die();
    }

    $constant = "<?php
    define('VERSION_NUMBER','1.5');
    define('VERSION_NAME','Beta');

    //Host de Mysql, le plus souvent localhost ou 127.0.0.1
    define('MYSQL_HOST','".$_['mysqlHost']."');
    //Identifiant MySQL
    define('MYSQL_LOGIN','".$_['mysqlLogin']."');
    //mot de passe MySQL
    define('MYSQL_MDP','".$_['mysqlMdp']."');
    //Nom de la base MySQL ou se trouvera leed
    define('MYSQL_BDD','".$_['mysqlBase']."');
    //Prefix des noms des tables leed pour les bases de données uniques
    define('MYSQL_PREFIX','".$_['mysqlPrefix']."');
    //Theme graphique
    define('DEFAULT_THEME','marigolds');
    //Nombre de pages affichées dans la barre de pagination
    define('PAGINATION_SCALE',5);
    //Nombre de flux mis à jour lors de la synchronisation graduée
    define('SYNC_GRAD_COUNT',10);
    //Langue utilisée
    define('LANGUAGE','fr');
?>";

    file_put_contents('constant.php', $constant);

    require_once('constant.php');
    require_once('MysqlEntity.class.php');
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
        $folder->setName('Général');
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
    $configurationManager->add('articleDisplayAnonymous','1');
    $configurationManager->add('articlePerPages','5');
    $configurationManager->add('articleDisplayLink','1');
    $configurationManager->add('articleDisplayDate','1');
    $configurationManager->add('articleDisplayAuthor','1');
    $configurationManager->add('articleDisplayHomeSort','1');
    $configurationManager->add('articleDisplayFolderSort','1');
    $configurationManager->add('synchronisationType','auto');
    $configurationManager->add('feedMaxEvents','300');
    $configurationManager->add('synchronisationCode',$synchronisationCode);
    $configurationManager->add('synchronisationEnableCache','1');
    $configurationManager->add('synchronisationForceFeed','0');
    $configurationManager->add('cryptographicSalt', $cryptographicSalt);

    header('location: settings.php#preferenceBloc');
    exit();
}else{
    if(!is_writable('./')){

        $test['Erreur'][]='Écriture impossible dans le répertoire Leed, veuillez ajouter les permissions en écriture sur tout le dossier (sudo chmod 777 -R '.str_replace(basename(__FILE__),'',__FILE__).', pensez à blinder les permissions par la suite)';
    }else{
        $test['Succès'][]='Permissions sur le dossier courant : OK';
    }
    if (!@function_exists('mysql_connect')){
        $test['Erreur'][] = 'La fonction requise "mysql_connect" est inaccessible sur votre serveur, verifiez vote installation de MySql.';
    }else{
        $test['Succès'][] = 'Fonction requise "mysql_connect" : OK';
    }
    if (!@function_exists('file_get_contents')){
        $test['Erreur'][] = 'La fonction requise "file_get_contents" est inaccessible sur votre serveur, verifiez votre version de PHP.';
    }else{
        $test['Succès'][] = 'Fonction requise "file_get_contents" : OK';
    }
    if (!@function_exists('file_put_contents')){
        $test['Erreur'][] = 'La fonction requise "file_put_contents" est inaccessible sur votre serveur, verifiez votre version de PHP.';
    }else{
        $test['Succès'][] = 'Fonction requise "file_put_contents" : OK';
    }
    if (@version_compare(PHP_VERSION, '5.1.0') <= 0){
        $test['Erreur'][] = 'Votre version de PHP ('.PHP_VERSION.') est trop ancienne, il est possible que certaines fonctionalitees du script comportent des disfonctionnements.';
    }else{
        $test['Succès'][] = 'Compabilité de version PHP ('.PHP_VERSION.') : OK';
    }
    if(ini_get('safe_mode') && ini_get('max_execution_time')!=0){
        $test['Erreur'][] = 'Le script ne peux pas gerer le timeout tout seul car votre safe mode est activé,<br/> dans votre fichier de configuration PHP, mettez la variable max_execution_time à 0 ou désactivez le safemode.';
    }else{
        $test['Succès'][] = 'Gestion du timeout : OK';
    }
?>
<div id="menuBar">
    <aside>
        <h3 class="left">Verifications</h3>
        <ul class="clear" style="margin:0">
    <?php
        foreach($test as $type=>$messages){
            $class = 'message ';
            $class .= 'Erreur'==$type ? 'messageError':'messageSuccess';
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
    <?php  if(!isset($test['Erreur'])){ ?>
    <form action="install.php" method="POST">
        <h1>Installation de Leed</h1>
        <table>
            <tr>
                <th colspan="2">Général</th>
            </tr>
            <tr>
                <td>Racine du projet</td>
                <td><input type="text" name="root" value="<?php echo str_replace(basename(__FILE__),'','http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>"></td>
            </tr>
            <tr>
                <th colspan="2">Mysql</th>
            </tr>
            <tr>
                <td>Hôte</td>
                <td><input type="text" name="mysqlHost" value="" placeholder="(Généralement 'localhost')"></td>
            </tr>
            <tr>
                <td>Identifiant</td>
                <td><input type="text" name="mysqlLogin" value=""></td>
            </tr>
            <tr>
                <td>Mot de passe</td>
                <td><input type="text" autocomplete="off" name="mysqlMdp" value="" placeholder="(sera affiché en clair)"></td>
            </tr>
            <tr>
                <td>Base</td>
                <td><input type="text" name="mysqlBase" value="" placeholder="(à créer avant)"></td>
            </tr>
            <tr>
                <td>Préfixe des tables</td>
                <td><input type="text" name="mysqlPrefix" value="leed_"></td>
            </tr>
            <tr>
                <th colspan="2">Administrateur</th>
            </tr>
            <tr>
                <td>Identifiant</td>
                <td><input type="text" name="login" placeholder="Identifiant"></td>
            </tr>
            <tr>
                <td>Mot de passe</td>
                <td><input type="text" autocomplete="off" name="password" placeholder="(sera affiché en clair)"></td>
            </tr>
        </table>
        <button id="installButton" name="installButton">Lancer l'installation</button>
    </form>
    <?php }else{ ?>
    <p>Il vous manque des prérequis pour continuer l'installation, référez vous au panneau de droite.</p>
    <?php }?>
    <?php } ?>
        </div> <!-- #main -->


    </div> <!-- #main-container -->

    <div id="footer-container">
        <footer class="wrapper">
            <p>Leed "Light Feed" by <a target="_blank" href="http://blog.idleman.fr">Idleman</a></p>
        </footer>
    </div>
</div>

</body>
</html>
