<?php

/*
 @nom: User
 @auteur: Idleman (idleman@idleman.fr)
 @description:  Classe de gestion des utilisateurs
 */

class User extends MysqlEntity{

    const TABLE_NAME = MYSQL_PREFIX.'user';
    const SESSION_OVERRIDE = 'userLogin';
    const OTP_INTERVAL = 30;
    const OTP_DIGITS   = 8;
    const OTP_DIGEST   = 'sha1';
    private $otpControler;

    protected $id,$login,$password,$otpSecret,$salt;
    protected $object_fields =
    array(
        'id'=>'key',
        'login'=>'string',
        'password'=>'string',
        'otpSecret'=>'string',
        'salt' => 'string',
        'conf' => 'longstring'
    );

    protected $object_fields_uniques =
    array(
        'login'
    );

    protected $conf = '{
        "articleDisplayAuthor": "1",
        "articleDisplayDate": "1",
        "articleDisplayFolderSort": "1",
        "articleDisplayHomeSort": "1",
        "articleDisplayLink": "1",
        "articleDisplayMode": "summary",
        "articlePerPages": "5",
        "displayOnlyUnreadFeedFolder": "false",
        "feedMaxEvents": "50",
        "optionFeedIsVerbose": 1,
        "paginationScale": 5,
        "synchronisationEnableCache": "0",
        "synchronisationForceFeed": "0",
        "synchronisationType": "auto"
    }';

    function __construct(){
        parent::__construct();
    }

    function setId($id){
        $this->id = $id;
    }

    function isOtpSecretValid($otpSecret) {
        // Teste si la longueur est d'au moins 8 caractères
        // et en Base32: [A-Z] + [2-7]
        return is_string($otpSecret) && preg_match('/^[a-zA-Z2-7]{8,}$/', $otpSecret);
    }

    protected function getOtpControler() {
        return new \OTPHP\TOTP($this->otpSecret, array('interval'=>self::OTP_INTERVAL, 'digits'=>self::OTP_DIGITS, 'digest'=>self::OTP_DIGEST));
    }

    function getOtpKey() {
        $otp = $this->getOtpControler();
        return str_pad($otp->now(), $otp->digits, '0', STR_PAD_LEFT);
    }

    function exist($login,$password,$otpEntered=Null){
        $userManager = new User();
        $user = $userManager->load(array('login'=>$login));

        if ($user instanceof User && $this->isPasswordMatched($user, $password)) {
            $otpSecret = $user->otpSecret;

            global $configurationManager;
            switch (True) {
                case !$configurationManager->get('otpEnabled'):
                case empty($otpSecret) && empty($otpEntered):
                    // Pas d'OTP s'il est désactivé dans la configuration où s'il n'est pas demandé et fourni.
                    return $user;
            }
            $otp = $user->getOtpControler();
            if ($otp->verify($otpEntered) || $otp->verify($otpEntered, time()-10)) {
                return $user;
            }
        }

        return false;
    }

    protected function isPasswordMatched($user, $dirtyPassword) {
        return $this->encrypt($dirtyPassword,$user->getSalt()) === $user->getPassword();
    }

    public function changePassword($dirtyPassword) {
        $logger = new Logger('settings');
        $newSalt = $this->generateSalt();
        $newPassword = $this->encrypt($dirtyPassword, $newSalt);
        $this->__construct();
        $this->change(
            array(
                'password' => $newPassword,
                'salt' => $newSalt
            ),
            array('id' => $this->getId())
        );
        $logger->appendLogs("Votre mot de passe a bien été mis à jour.");
        $logger->save();
    }

    static function get($login){
        $userManager = new User();
        return $userManager->load(array('login'=>$login,));
    }

    function getToken() {
        assert('!empty($this->password)');
        assert('!empty($this->login)');
        return sha1($this->password.$this->login);
    }

    static function existAuthToken($auth=null){
        $result = false;
        $userManager = new User();
        $users = $userManager->populate('id');
        $phpAuth = strtolower(@$_SERVER['PHP_AUTH_USER']);
        if (empty($auth)) $auth = @$_COOKIE['leedStaySignedIn'];
        foreach($users as $user){
            if ($user->getToken()==$auth || strtolower($user->login)===$phpAuth){
                $result = $user;
                break;
            }
        }
        return $result;
    }

    function setStayConnected() {
        ///@TODO: set the current web directory, here and on del
        setcookie('leedStaySignedIn', $this->getToken(), time()+31536000);
    }

    static function delStayConnected() {
        setcookie('leedStaySignedIn', '', -1);
    }

    public function getUserList() {
        return $this->loadAllOnlyColumn(
            '`'.$this::TABLE_NAME.'`.`id`,'.
            '`'.$this::TABLE_NAME.'`.`login`',
            array('id' => 1),
            '`id` ASC',
            null,
            '>='
        );
    }

    public function add($login = false, $password = false, $logger = false) {
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
        $this->setPassword($password, $this->generateSalt());
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
        $_SESSION[$this::SESSION_OVERRIDE] = $this->getLogin();
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
        unset($_SESSION[$this::SESSION_OVERRIDE]);
    }

    public function updateConf($newConfs) {
        $confs = $this->getConf();
        foreach($newConfs as $key => $value) {
            $confs->$key = $value;
        }
        $this->setConf($confs);
        parent::__construct();
        $this->change(
            array(
                'conf' => $this->conf
            ),
            array('id' => $this->getId())
        );
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
        $this->setSalt($salt);
    }

    public function getSalt() {
        return $this->salt;
    }

    function setSalt($salt){
        $this->salt = $salt;
    }

    public function getConf() {
        return json_decode($this->conf);
    }

    public function setConf($conf) {
        $this->conf = json_encode($conf);
    }

    function getOtpSecret(){
        return $this->otpSecret;
    }

    function setOtpSecret($otpSecret){
        $this->otpSecret = $otpSecret;
    }

    public function resetPassword($resetPassword){
        $this->setPassword($resetPassword, $this->getSalt());
        $this->otpSecret = '';
        $this->save();
    }

    static function encrypt($password, $salt=''){
        return sha1($password.$salt);
    }

    static function generateSalt() {
        return ''.mt_rand().mt_rand();
    }

}

?>
