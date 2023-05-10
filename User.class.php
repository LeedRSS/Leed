<?php

/*
 @nom: User
 @auteur: Idleman (http://blog.idleman.fr)
 @description:  Classe de gestion des utilisateurs
 */

class User extends MysqlEntity{

    protected $id;
    protected $login;
    protected $password;
    protected $TABLE_NAME = 'user';
    protected $object_fields =
    array(
        'id'=>'key',
        'login'=>'string',
        'password'=>'string'
    );

    protected $object_fields_uniques =
    array(
        'login'
    );

    function __construct(){
        parent::__construct();
    }

    function setId($id){
        $this->id = $id;
    }

    function exist($login,$password,$salt=''){
        $userManager = new User();
        return $userManager->load(array('login'=>$login,'password'=>User::encrypt($password,$salt)));
    }

    static function get($login){
        $userManager = new User();
        return $userManager->load(array('login'=>$login,));
    }

    function getToken() {
        $hasPassword = !empty($this->password);
        $hasLogin = !empty($this->login);
        assert($hasLogin);
        assert($hasPassword);
        return sha1($this->password.$this->login);
    }

    public function add($login = false, $password = false, $salt = false, $logger = false) {
        if(!$logger) {
            require_once('Logger.class.php');
            $logger = new Logger('settings');
        }
        if(empty($login)) {
            $logger->appendLogs(_t("USER_ADD_MISSING_LOGIN"));
        }
        $existingUser = $this->load(array('login' => $login));
        if($existingUser instanceof User) {
            $logger->appendLogs(_t("USER_ADD_DUPLICATE"));
            $logger->save();
            return false;
        }
        if(empty($password)) {
            $logger->appendLogs(_t("USER_ADD_MISSING_PASSWORD"));
        }
        if($logger->hasLogs()) {
            $logger->save();
            return false;
        }
        $this->setLogin($login);
        $this->setPassword($password, $salt);
        $this->save();
        $this->createSideTables($login);
        $logger->appendLogs(_t("USER_ADD_OK"). ' '.$login);
        $logger->save();
        return true;
    }

    public function remove($userId) {
        require_once('Logger.class.php');
        $logger = new Logger('settings');
        if(empty($userId)) {
            $logger->appendLogs(_t("USER_DEL_MISSING_ID"));
            $logger->save();
            return false;
        }
        $user = $this->load(array('id' => $userId));
        if(!$user) {
            $logger->appendLogs(_t("USER_DEL_UNKNOWN_ID").' '.$userId);
            $logger->save();
            return false;
        }
        $this->setLogin($user->getLogin());
        $this->deleteSideTables();
        $this->delete(array('id' => $userId));
        $logger->appendLogs(_t("USER_DEL_OK").$user->getLogin());
        $logger->save();
        return true;
    }

    protected function createSideTables() {
        $this->manageSideTables();
    }

    protected function deleteSideTables() {
        $this->manageSideTables('remove');
    }

    protected function manageSideTables($action = 'add') {
        $actionMethod = $action === 'add' ? 'create' : 'destroy';
        $feedManager = new Feed();
        $feedManager->$actionMethod();
        $eventManager = new Event();
        $eventManager->$actionMethod();
        $folderManager = new Folder();
        $folderManager->$actionMethod();
        if($action === 'add' && $folderManager->rowCount() === '0') {
            $folderManager->setName(_t('GENERAL_FOLDER'));
            $folderManager->setParent(-1);
            $folderManager->setIsopen(1);
            $folderManager->save();
        }
    }

    static function existAuthToken($auth=null){
        $result = false;
        $userManager = new User();
        $users = $userManager->populate('id');
        $phpAuth = isset($_SERVER['PHP_AUTH_USER']) ? strtolower($_SERVER['PHP_AUTH_USER']) : false;
        if (empty($auth)) $auth = @$_COOKIE['leedStaySignedIn'];
        foreach($users as $user){
            if ($user->getToken()==$auth || strtolower($user->login)===$phpAuth){
                $result = $user;
                break;
            }
        }
        return $result;
    }

    static function generateSalt() {
        return ''.mt_rand().mt_rand();
    }

    function setStayConnected() {
        ///@TODO: set the current web directory, here and on del
        setcookie('leedStaySignedIn', $this->getToken(), time()+31536000);
    }

    static function delStayConnected() {
        setcookie('leedStaySignedIn', '', -1);
    }

    function getId(){
        return $this->id;
    }

    function getLogin(){
        return $this->login;
    }

    function setLogin($login){
        $this->login = $login;
    }

    function getPassword(){
        return $this->password;
    }

    function setPassword($password,$salt=''){
        $this->password = User::encrypt($password,$salt);
    }

    function resetPassword($resetPassword, $salt=''){
        $this->setPassword($resetPassword, $salt);
        $this->save();
    }

    static function encrypt($password, $salt=''){
        return sha1($password.$salt);
    }

}

?>
