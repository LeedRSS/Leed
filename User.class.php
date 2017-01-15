<?php

/*
 @nom: User
 @auteur: Idleman (idleman@idleman.fr)
 @description:  Classe de gestion des utilisateurs
 */

class User extends MysqlEntity{

    protected $id,$login,$password,$otpSeed;
    protected $TABLE_NAME = 'user';
    protected $CLASS_NAME = 'User';
    protected $object_fields =
    array(
        'id'=>'key',
        'login'=>'string',
        'password'=>'string',
        'otpSeed'=>'string',
    );

    function __construct(){
        parent::__construct();
    }

    function setId($id){
        $this->id = $id;
    }

    function exist($login,$password,$salt='',$otpEntered=Null){
        $userManager = new User();
        $user = $userManager->load(array('login'=>$login,'password'=>User::encrypt($password,$salt)));

        if (false!=$user) {
            $otpSeed = $user->otpSeed;

            switch (True) {
                #case !$configuration->get('otpEnabled'):
                case empty($otpSeed) && empty($otpEntered):
                    // Pas d'OTP s'il est désactivé dans la configuration où s'il n'est pas demandé et fourni.
                    return $user;
            }
            $otp = new \OTPHP\TOTP($otpSeed, array('interval'=>30, 'digits'=>8, 'digest'=>'sha1'));
            if ($otp->verify($otpEntered) || $otp->verify($otpEntered, time()-10)) {
                return $user;
            }
        }

        return false;
    }

    function get($login){
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

    function getOtpSeed(){
        return $this->otpSeed;
    }

    function setOtpSeed($otpSeed){
        return $this->otpSeed = $otpSeed;
    }

    function resetPassword($resetPassword, $salt=''){
        $this->setPassword($resetPassword, $salt);
        $this->otpSeed = '';
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
