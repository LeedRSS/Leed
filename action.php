<?php

/*
 @nom: action
 @auteur: Idleman (http://blog.idleman.fr)
 @description: Page de gestion des évenements non liés a une vue particulière (appels ajax, requetes sans resultats etc...)
 */

if(!ini_get('safe_mode')) @set_time_limit(0);
require_once("common.php");

///@TODO: déplacer dans common.php?
$commandLine = 'cli'==php_sapi_name();

if ($commandLine) {
    $action = 'commandLine';
} else {
    $action = @$_['action'];
}
///@TODO: pourquoi ne pas refuser l'accès dès le début ?
Plugin::callHook("action_pre_case", array(&$_,$myUser));

//Execution du code en fonction de l'action
switch ($action){
    case 'commandLine':
    case 'synchronize':
        require_once("SimplePie.class.php");
        $syncCode = $configurationManager->get('synchronisationCode');
        $syncGradCount = $configurationManager->get('syncGradCount');
        if (   false==$myUser
            && !$commandLine
            && !(isset($_['code'])
                && $configurationManager->get('synchronisationCode')!=null
                && $_['code']==$configurationManager->get('synchronisationCode')
            )
        ) {
            die(_t('YOU_MUST_BE_CONNECTED_ACTION'));
        }
        Functions::triggerDirectOutput();

        if (!$commandLine){
            echo '<html>
                <head>
                <link rel="stylesheet" href="./templates/'.$theme.'/css/style.css">
                <meta name="referrer" content="no-referrer" />
                </head>
                <body>
                <div class="sync">';
        }
        $synchronisationType = $configurationManager->get('synchronisationType');

        $synchronisationCustom = array();
        Plugin::callHook("action_before_synchronisationtype", array(&$synchronisationCustom,&$synchronisationType,&$commandLine,$configurationManager,$start));
        if(isset($synchronisationCustom['type'])){
            $feeds = $synchronisationCustom['feeds'];
            $syncTypeStr = _t('SYNCHRONISATION_TYPE').' : '._t($synchronisationCustom['type']);
        }elseif('graduate'==$synchronisationType){
            // sélectionne les 10 plus vieux flux
            $feeds = $feedManager->loadAll(null,'lastupdate', $syncGradCount);
            $syncTypeStr = _t('SYNCHRONISATION_TYPE').' : '._t('GRADUATE_SYNCHRONISATION');
        }else{
            // sélectionne tous les flux, triés par le nom
            $feeds = $feedManager->populate('name');
            $syncTypeStr = _t('SYNCHRONISATION_TYPE').' : '._t('FULL_SYNCHRONISATION');
        }

        if(!isset($synchronisationCustom['no_normal_synchronize'])){
            $feedManager->synchronize($feeds, $syncTypeStr, $commandLine, $configurationManager, $start);
        }
    break;


    case 'readAll':
        if($myUser==false) exit(_t('YOU_MUST_BE_CONNECTED_ACTION'));
        $whereClause = array();
        $whereClause['unread'] = '1';
        if(isset($_['feed']))$whereClause['feed'] = $_['feed'];
        if(isset($_['last-event-id']))$whereClause['id'] = '<= ' . $_['last-event-id'];
        $eventManager->change(array('unread'=>'0'),$whereClause);
        if(!Functions::isAjaxCall()){
            header('location: ./');
        }
    break;

    case 'readFolder':
        if($myUser==false) exit(_t('YOU_MUST_BE_CONNECTED_ACTION'));

        $feeds = $feedManager->loadAllOnlyColumn('id',array('folder'=>$_['folder']));

        foreach($feeds as $feed){
            $whereClause['feed'] = $feed->getId();
            if(isset($_['last-event-id']))$whereClause['id'] = '<= ' . $_['last-event-id'];
            $eventManager->change(array('unread'=>'0'),$whereClause);
        }

        if (!Functions::isAjaxCall()){
            header('location: ./');
        }

    break;

    case 'updateConfiguration':
        if($myUser==false) exit(_t('YOU_MUST_BE_CONNECTED_ACTION'));

            //Ajout des préférences et réglages
            $configurationManager->put('root',(substr($_['root'], strlen($_['root'])-1)=='/'?$_['root']:$_['root'].'/'));
            $configurationManager->put('articleDisplayAnonymous',$_['articleDisplayAnonymous']);
            $configurationManager->put('articlePerPages',$_['articlePerPages']);
            $configurationManager->put('articleDisplayLink',$_['articleDisplayLink']);
            $configurationManager->put('articleDisplayDate',$_['articleDisplayDate']);
            $configurationManager->put('articleDisplayAuthor',$_['articleDisplayAuthor']);
            $configurationManager->put('articleDisplayHomeSort',$_['articleDisplayHomeSort']);
            $configurationManager->put('articleDisplayFolderSort',$_['articleDisplayFolderSort']);
            $configurationManager->put('articleDisplayMode',$_['articleDisplayMode']);
            $configurationManager->put('synchronisationType',$_['synchronisationType']);
            $configurationManager->put('synchronisationEnableCache',$_['synchronisationEnableCache']);
            $configurationManager->put('synchronisationForceFeed',$_['synchronisationForceFeed']);
            $configurationManager->put('feedMaxEvents',$_['feedMaxEvents']);
            $configurationManager->put('language',$_['ChgLanguage']);
            $configurationManager->put('theme',$_['ChgTheme']);
            $configurationManager->put('otpEnabled',$_['otpEnabled']);

            if(trim($_['password'])!='') {
                $salt = User::generateSalt();
                $userManager->change(array('password'=>User::encrypt($_['password'], $salt)),array('id'=>$myUser->getId()));
                /* /!\ En multi-utilisateur, il faudra changer l'information au
                niveau du compte lui-même et non au niveau du déploiement comme
                ici. C'est ainsi parce que c'est plus efficace de stocker le sel
                dans la config que dans le fichier de constantes, difficile à
                modifier. */
                $oldSalt = $configurationManager->get('cryptographicSalt');
                if (empty($oldSalt))
                    /* Pendant la migration à ce système, les déploiements
                    ne posséderont pas cette donnée. */
                    $configurationManager->add('cryptographicSalt', $salt);
                else
                    $configurationManager->change(array('value'=>$salt), array('key'=>'cryptographicSalt'));

            }

            # Modifications dans la base de données, la portée courante et la sesssion
            # @TODO: gérer cela de façon centralisée
            $otpSecret = $_['otpSecret'];
            if ($myUser->isOtpSecretValid($otpSecret)) {
                $userManager->change(array('login'=>$_['login'], 'otpSecret'=>$otpSecret),array('id'=>$myUser->getId()));
                $myUser->setLogin($_['login']);
                $myUser->setOtpSecret($otpSecret);
                $_SESSION['currentUser'] = serialize($myUser);
            }

    header('location: ./settings.php#preferenceBloc');
    break;


    case 'purgeEvents':
        if($myUser==false) exit(_t('YOU_MUST_BE_CONNECTED_ACTION'));
        $eventManager->truncate();
        header('location: ./settings.php');
    break;


    case 'purgeCache':
        if($myUser==false) exit(_t('YOU_MUST_BE_CONNECTED_ACTION'));
        Functions::purgeRaintplCache();
        header('location: ./settings.php');
    break;


    case 'exportFeed':
        if($myUser==false) exit(_t('YOU_MUST_BE_CONNECTED_ACTION'));
            /*********************/
        /** Export **/
        /*********************/
        if(isset($_POST['exportButton'])){
            $opml = new Opml();
            $xmlStream = $opml->export();

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
        if($myUser==false) exit(_t('YOU_MUST_BE_CONNECTED_ACTION'));
        echo '<html style="height:auto;"><link rel="stylesheet" href="templates/'.$theme.'/css/style.css">
                <body style="height:auto;">
                    <form action="action.php?action=importFeed" method="POST" enctype="multipart/form-data">
                    <p>'._t('OPML_FILE').' : <input name="newImport" type="file"/> <button name="importButton">'._t('IMPORT').'</button></p>
                    <p>'._t('IMPORT_COFFEE_TIME').'</p>
                    </form>
                </body>
            </html>

            ';
    break;

    case 'synchronizeForm':
     if(isset($myUser) && $myUser!=false){
        echo '<link rel="stylesheet" href="templates/'.$theme.'/css/style.css">
                <a class="button" href="action.php?action=synchronize">'._t('SYNCHRONIZE_NOW').'</a>
                    <p>'._t('SYNCHRONIZE_COFFEE_TIME').'</p>

            ';
        }else{
            echo _t('YOU_MUST_BE_CONNECTED_ACTION');
        }

    break;

    case 'changeFolderState':
        if($myUser==false) exit(_t('YOU_MUST_BE_CONNECTED_ACTION'));
        $folderManager->change(array('isopen'=>$_['isopen']),array('id'=>$_['id']));
    break;

    case 'importFeed':
        // On ne devrait pas mettre de style ici.
        echo "<html>
            <style>
                a {
                    color:#F16529;
                }

                html,body{
                        font-family:Verdana;
                        font-size: 11px;
                }
                .error{
                        background-color:#C94141;
                        color:#ffffff;
                        padding:5px;
                        border-radius:5px;
                        margin:10px 0px 10px 0px;
                        box-shadow: 0 0 3px 0 #810000;
                    }
                .error a{
                        color:#ffffff;
                }
                </style>
            </style><body>
\n";
        if($myUser==false) exit(_t('YOU_MUST_BE_CONNECTED_ACTION'));
        if(!isset($_POST['importButton'])) break;
        $opml = new Opml();
        echo "<h3>"._t('IMPORT')."</h3><p>"._t('PENDING')."</p>\n";
        try {
            $errorOutput = $opml->import($_FILES['newImport']['tmp_name']);
        } catch (Exception $e) {
            $errorOutput = array($e->getMessage());
        }
        if (empty($errorOutput)) {
            echo "<p>"._t('IMPORT_NO_PROBLEM')."</p>\n";
        } else {
            echo "<div class='error'>"._t('IMPORT_ERROR')."\n";
            foreach($errorOutput as $line) {
                echo "<p>$line</p>\n";
            }
            echo "</div>";
        }
        if (!empty($opml->alreadyKnowns)) {
            echo "<h3>"._t('IMPORT_FEED_ALREADY_KNOWN')." : </h3>\n<ul>\n";
            foreach($opml->alreadyKnowns as $alreadyKnown) {
                foreach($alreadyKnown as &$elt) $elt = htmlspecialchars($elt);
                $text = Functions::truncate($alreadyKnown->feedName, 60);
                echo "<li><a target='_parent' href='{$alreadyKnown->xmlUrl}'>"
                    ."{$text}</a></li>\n";
            }
            echo "</ul>\n";
        }
        $syncLink = "action.php?action=synchronize&format=html";
        echo "<p>";
        echo "<a href='$syncLink' style='text-decoration:none;font-size:3em'>"
            ."↺</a>";
        echo "<a href='$syncLink'>"._t('CLIC_HERE_SYNC_IMPORT')."</a>";
        echo "<p></body></html>\n";
    break;


    case 'addFeed':
        if($myUser==false) exit(_t('YOU_MUST_BE_CONNECTED_ACTION'));
        require_once("SimplePie.class.php");
        if(!isset($_['newUrl'])) break;
        $newFeed = new Feed();
        $newFeed->setUrl(Functions::clean_url($_['newUrl']));
        if ($newFeed->notRegistered()) {
            $newFeed->setFolder(
                (isset($_['newUrlCategory'])?$_['newUrlCategory']:1)
            );
            $newFeed->save();
            $enableCache = ($configurationManager->get('synchronisationEnableCache')=='')?0:$configurationManager->get('synchronisationEnableCache');
            $forceFeed = ($configurationManager->get('synchronisationForceFeed')=='')?0:$configurationManager->get('synchronisationForceFeed');
            $newFeed->parse(time(), $_, $enableCache, $forceFeed);
            Plugin::callHook("action_after_addFeed", array(&$newFeed));
        } else {
            $logger = new Logger('settings');
            $logger->appendLogs(_t("FEED_ALREADY_STORED"));
            $logger->save();
        }
        header('location: ./settings.php#manageBloc');
    break;

    case 'changeFeedFolder':
        if($myUser==false) exit(_t('YOU_MUST_BE_CONNECTED_ACTION'));
        if(isset($_['feed'])){
            $feedManager->change(array('folder'=>$_['folder']),array('id'=>$_['feed']));
        }
        header('location: ./settings.php');
    break;

    case 'removeFeed':
        if($myUser==false) exit(_t('YOU_MUST_BE_CONNECTED_ACTION'));
        if(isset($_GET['id'])){
            $feedManager->delete(array('id'=>$_['id']));
            $eventManager->delete(array('feed'=>$_['id']));
            Plugin::callHook("action_after_removeFeed", array($_['id']));
        }
        header('location: ./settings.php');
    break;

    case 'addFolder':
        if($myUser==false) exit(_t('YOU_MUST_BE_CONNECTED_ACTION'));
        if(isset($_['newFolder'])){
            $folder = new Folder();
            if($folder->rowCount(array('name'=>$_['newFolder']))==0){
                $folder->setParent(-1);
                $folder->setIsopen(0);
                $folder->setName($_['newFolder']);
                $folder->save();
            }
        }
        header('location: ./settings.php');
    break;


    case 'renameFolder':
        if($myUser==false) exit(_t('YOU_MUST_BE_CONNECTED_ACTION'));
        if(isset($_['id'])){
            $folderManager->change(array('name'=>$_['name']),array('id'=>$_['id']));
        }
    break;

    case 'renameFeed':
        if($myUser==false) exit(_t('YOU_MUST_BE_CONNECTED_ACTION'));
        if(isset($_['id'])){
            $feedManager->change(array('name'=>$_['name'],'url'=>Functions::clean_url($_['url'])),array('id'=>$_['id']));
        }
    break;

    case 'removeFolder':
        if($myUser==false) exit(_t('YOU_MUST_BE_CONNECTED_ACTION'));
        if(isset($_['id']) && is_numeric($_['id']) && $_['id']>0){
            $eventManager->customQuery('DELETE FROM `'.MYSQL_PREFIX.'event` WHERE `'.MYSQL_PREFIX.'event`.`feed` in (SELECT `'.MYSQL_PREFIX.'feed`.`id` FROM `'.MYSQL_PREFIX.'feed` WHERE `'.MYSQL_PREFIX.'feed`.`folder` =\''.intval($_['id']).'\') ;');
            $feedManager->delete(array('folder'=>$_['id']));
            $folderManager->delete(array('id'=>$_['id']));
        }
        header('location: ./settings.php');
    break;

    case 'readContent':
        if($myUser==false) {
            $response_array['status'] = 'noconnect';
            $response_array['texte'] = _t('YOU_MUST_BE_CONNECTED_ACTION');
            header('Content-type: application/json');
            echo json_encode($response_array);
            exit();
        }
        if(isset($_['id'])){
            $event = $eventManager->load(array('id'=>$_['id']));
            $eventManager->change(array('unread'=>'0'),array('id'=>$_['id']));
        }
    break;

    case 'unreadContent':
        if($myUser==false) {
            $response_array['status'] = 'noconnect';
            $response_array['texte'] = _t('YOU_MUST_BE_CONNECTED_ACTION');
            header('Content-type: application/json');
            echo json_encode($response_array);
            exit();
        }
        if(isset($_['id'])){
            $event = $eventManager->load(array('id'=>$_['id']));
            $eventManager->change(array('unread'=>'1'),array('id'=>$_['id']));
        }
    break;

    case 'addFavorite':
        if($myUser==false) {
            $response_array['status'] = 'noconnect';
            $response_array['texte'] = _t('YOU_MUST_BE_CONNECTED_ACTION');
            header('Content-type: application/json');
            echo json_encode($response_array);
            exit();
        }
        $eventManager->change(array('favorite'=>'1'),array('id'=>$_['id']));
    break;

    case 'removeFavorite':
        if($myUser==false) {
            $response_array['status'] = 'noconnect';
            $response_array['texte'] = _t('YOU_MUST_BE_CONNECTED_ACTION');
            header('Content-type: application/json');
            echo json_encode($response_array);
            exit();
        }
        $eventManager->change(array('favorite'=>'0'),array('id'=>$_['id']));
    break;

    case 'login':

        define('RESET_PASSWORD_FILE', 'resetPassword');
        if (file_exists(RESET_PASSWORD_FILE)) {
            /* Pour réinitialiser le mot de passe :
             * créer le fichier RESET_PASSWORD_FILE vide.
             * Le nouveau mot de passe sera celui fourni à la connexion.
             */
            @unlink(RESET_PASSWORD_FILE);
            if (file_exists(RESET_PASSWORD_FILE)) {
                $message = 'Unable to remove "'.RESET_PASSWORD_FILE.'"!';
                /* Pas supprimable ==> on ne remet pas à zéro */
            } else {
                $resetPassword = $_['password'];
                assert('!empty($resetPassword)');
                $tmpUser = User::get($_['login']);
                if (false===$tmpUser) {
                    $message = "Unknown user '{$_['login']}'! No password reset.";
                } else {
                    $tmpUser->resetPassword($resetPassword, $configurationManager->get('cryptographicSalt'));
                    $message = "User '{$_['login']}' (id={$tmpUser->getId()}) Password reset to '$resetPassword'.";
                }
            }
            error_log($message);
        }

        if(isset($_['usr'])){
            $user = User::existAuthToken($_['usr']);
            if($user==false){
                exit("error"); //@TODO: traduire
            }else{
                $_SESSION['currentUser'] = serialize($user);
                header('location: ./action.php?action=addFeed&newUrl='.$_['newUrl']);
                exit();
            }
        }else{
            $salt = $configurationManager->get('cryptographicSalt');
            if (empty($salt)) $salt = '';
            $user = $userManager->exist($_['login'],$_['password'],$salt,@$_['otp']);
            if($user==false){
                error_log("Leed: wrong login for '".$_['login']."'");
                header('location: ./?action=wrongLogin');
            }else{
                $_SESSION['currentUser'] = serialize($user);
                if (isset($_['rememberMe'])) $user->setStayConnected();
                header('location: ./');
            }
            exit();
        }



    break;

    case 'changePluginState':
        if($myUser==false) exit(_t('YOU_MUST_BE_CONNECTED_ACTION'));

        if($_['state']=='0'){
            Plugin::enabled($_['plugin']);

        }else{
            Plugin::disabled($_['plugin']);
        }
        header('location: ./settings.php#pluginBloc');
    break;



    case 'logout':
        User::delStayConnected();
        $_SESSION = array();
        session_unset();
        session_destroy();
        header('location: ./');
    break;

    case 'displayOnlyUnreadFeedFolder':
        if($myUser==false) {
            $response_array['status'] = 'noconnect';
            $response_array['texte'] = _t('YOU_MUST_BE_CONNECTED_ACTION');
            header('Content-type: application/json');
            echo json_encode($response_array);
            exit();
        }
        $configurationManager->put('displayOnlyUnreadFeedFolder',$_['displayOnlyUnreadFeedFolder']);
    break;

    case 'displayFeedIsVerbose':
        if($myUser==false) {
            $response_array['status'] = 'noconnect';
            $response_array['texte'] = _t('YOU_MUST_BE_CONNECTED_ACTION');
            header('Content-type: application/json');
            echo json_encode($response_array);
            exit();
        }
        // changement du statut isverbose du feed
        $feed = new Feed();
        $feed = $feed->getById($_['idFeed']);
        $feed->setIsverbose(($_['displayFeedIsVerbose']=="0"?1:0));
        $feed->save();
        break;

    case 'optionFeedIsVerbose':
        if($myUser==false) {
            $response_array['status'] = 'noconnect';
            $response_array['texte'] = _t('YOU_MUST_BE_CONNECTED_ACTION');
            header('Content-type: application/json');
            echo json_encode($response_array);
            exit();
        }
        // changement du statut de l'option
        $configurationManager = new Configuration();
        $conf = $configurationManager->getAll();
        $configurationManager->put('optionFeedIsVerbose',($_['optionFeedIsVerbose']=="0"?0:1));

        break;

    case 'articleDisplayMode':
        if($myUser==false) {
            $response_array['status'] = 'noconnect';
            $response_array['texte'] = _t('YOU_MUST_BE_CONNECTED_ACTION');
            header('Content-type: application/json');
            echo json_encode($response_array);
            exit();
        }
        // chargement du content de l'article souhaité
        $newEvent = new Event();
        $event = $newEvent->getById($_['event_id']);

        if ($_['articleDisplayMode']=='content'){
            //error_log(print_r($_SESSION['events'],true));
            $content = $event->getContent();
        } else {
            $content = $event->getDescription();
        }
        echo $content;

        break;

    default:
        require_once("SimplePie.class.php");
        Plugin::callHook("action_post_case", array(&$_,$myUser));
        //exit('0');
    break;

    //Installation d'un nouveau plugin
    case 'installPlugin':
        Plugin::install($_['zip']);
    break;
    case 'getGithubMarket':
        $plugin = new Plugin();
        $plugin->getGithubMarketRepos();
    break;
}


?>
