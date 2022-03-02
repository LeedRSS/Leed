<?php
require_once('i18n.php');
class_exists('MysqlEntity') or require_once('MysqlEntity.class.php');
class_exists('User') or require_once('User.class.php');
class_exists('Configuration') or require_once('Configuration.class.php');
$configurationManager = new Configuration();
$conf = $configurationManager->getAll();

$theme = $configurationManager->get('theme');
$myUser = (isset($_SESSION['currentUser'])?unserialize($_SESSION['currentUser']):false);
if (empty($myUser)) {
    /* Pas d'utilisateur dans la session ?
     * On tente de récupérer une nouvelle session avec un jeton. */
    $myUser = User::existAuthToken();
    $_SESSION['currentUser'] = serialize($myUser);
}
if (!$myUser) {
    $languages = Translation::getHttpAcceptLanguages();
} else {
    $languages = array($configurationManager->get('language'));
}

i18n_init($languages, dirname(__FILE__).'/templates/'.$theme.'/');
header('Content-Type: application/javascript; charset=utf-8');
echo "var  i18n = {$i18n_js};";
