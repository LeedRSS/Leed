<?php

/*
 @nom: User
 @auteur: Idleman (idleman@idleman.fr)
 @description:  Classe de gestion des utilisateurs
 */

class User extends MysqlEntity{

	protected $id,$login,$password,$prefixDatabase;
	protected $TABLE_NAME = 'user';
	protected $CLASS_NAME = 'User';
	protected $object_fields = 
	array(
		'id'=>'key',
		'login'=>'string',
		'password'=>'string',
		'prefixDatabase'=>'string'
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

	function existAuthToken($auth){
		$result = false;
		$userManager = new User();
		$users = $userManager->populate('id');
		foreach($users as $user){
		
			if(sha1($user->getPassword().$user->getLogin())==$auth) $result = $user;
		}
		return $result;
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

	function setPrefixDatabase($prefix){
		$this->prefixDatabase = $prefix;
	}

	function getprefixDatabase(){
		return $this->prefixDatabase;
	}
}

?>