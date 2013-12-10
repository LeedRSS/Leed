<?php

/*
 @nom: User
 @auteur: Idleman (idleman@idleman.fr)
 @description:  Classe de gestion des utilisateurs
 */

class User extends MysqlEntity{

	protected $id,$login,$password;
	protected $TABLE_NAME = 'user';
	protected $CLASS_NAME = 'User';
	protected $object_fields = 
	array(
		'id'=>'key',
		'login'=>'string',
		'password'=>'string'
	);

	function __construct(){
		parent::__construct();
	}

	function setId($id){
		$this->id = $id;
	}

	function exist($login,$password){
		$userManager = new User();
		return $userManager->load(array('login'=>$login,'password'=>User::encrypt($password)));
	}

	function getToken() {
		assert('!empty($this->password)');
		assert('!empty($this->login)');
		return sha1($this->password.$this->login);
	}

	function existAuthToken($auth=null){
		$result = false;
		$userManager = new User();
		$users = $userManager->populate('id');
		if (empty($auth)) $auth = @$_COOKIE['leedStaySignedIn'];
		error_log($auth);
		foreach($users as $user){
			if($user->getToken()==$auth) $result = $user;
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

	function setPassword($password){
		$this->password = User::encrypt($password);
	}

	static function encrypt($password){
		return sha1($password);
	}


}

?>