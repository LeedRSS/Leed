<?php

/*
 @nom: User
 @auteur: Idleman (idleman@idleman.fr)
 @description:  Classe de gestion des utilisateurs
 */

class User extends MysqlEntity{

    const OTP_INTERVAL = 30;
    const OTP_DIGITS   = 8;
    const OTP_DIGEST   = 'sha1';
    private $otpControler;

    protected $id,$login,$password,$otpSecret;
    protected $TABLE_NAME = 'user';
    protected $CLASS_NAME = 'User';
    protected $object_fields =
    array(
        'id'=>'key',
        'login'=>'string',
        'password'=>'string',
        'otpSecret'=>'string',
    );

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

    function exist($login,$password,$salt='',$otpEntered=Null){
        $userManager = new User();
        $user = $userManager->load(array('login'=>$login,'password'=>User::encrypt($password,$salt)));

        if (false!=$user) {
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

    function getOtpSecret(){
        return $this->otpSecret;
    }

    function setOtpSecret($otpSecret){
        $this->otpSecret = $otpSecret;
    }

    function resetPassword($resetPassword, $salt=''){
        $this->setPassword($resetPassword, $salt);
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
