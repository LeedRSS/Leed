<?php

/*
    @nom: mysql
    @auteur: Idleman (idleman@idleman.fr)
    @date de création:
    @description: Classe de gestion des connexions Mysql
*/

class MysqlConnector
{
    private $id;
    private $hote;
    private $login;
    private $mdp;
    private $bdd;
    private $port;
    public $debug=0;
    private $connection = null;
    public static $instance = null;

    private function __construct(){
        $this->connect();
    }



    /**
    * Methode de recuperation unique de l'instance
    * @author Valentin CARRUESCO
    * @category Singleton
    * @param <Aucun>
    * @return <mysql> $instance
    */

    public static function getInstance(){

        if (MysqlConnector::$instance === null) {
            MysqlConnector::$instance = new self();
        }
        return MysqlConnector::$instance;
    }



    public function connect(){
        $this->connection = mysql_connect(MYSQL_HOST,MYSQL_LOGIN,MYSQL_MDP);
        mysql_query('SET NAMES utf8');
        mysql_select_db(MYSQL_BDD,$this->connection);
    }



    public function __toString(){
        $retour = "";
        $retour .= "instance de la classe MysqlConnector : <br/>";
        $retour .= '$hote : '.$this->hote.'<br/>';
        $retour .= '$login : '.$this->login.'<br/>';
        $retour .= '$mdp : '.$this->mdp.'<br/>';
        $retour .= '$bdd : '.$this->bdd.'<br/>';
        $retour .= '$port : '.$this->port.'<br/>';
        return $retour;
    }

    private  function __clone(){
        //Action lors du clonage de l'objet
    }

    // ACCESSEURS

    public function getId(){
        return $this->id;
    }

    public function setId($id){
        $this->id = $id;
    }

    /**
    * Méthode de récuperation de l'attribut hote de la classe Mysql
    * @author Valentin CARRUESCO
    * @category Accesseur
    * @param Aucun
    * @return <Attribute> hote
    */

    public function getHote(){
        return $this->hote;
    }

    /**
    * Méthode de définition de l'attribut hote de la classe Mysql
    * @author Valentin CARRUESCO
    * @category Accesseur
    * @param <Attribute> $hote
    * @return Aucun retour
    */

    public function setHote($hote){
        $this->hote = $hote;
    }

    /**
    * Méthode de récuperation de l'attribut login de la classe Mysql
    * @author Valentin CARRUESCO
    * @category Accesseur
    * @param Aucun
    * @return <Attribute> login
    */

    public function getLogin(){
        return $this->login;
    }

    /**
    * Méthode de définition de l'attribut login de la classe Mysql
    * @author Valentin CARRUESCO
    * @category Accesseur
    * @param <Attribute> $login
    * @return Aucun retour
    */

    public function setLogin($login){
        $this->login = $login;
    }

    /**
    * Méthode de récuperation de l'attribut mdp de la classe Mysql
    * @author Valentin CARRUESCO
    * @category Accesseur
    * @param Aucun
    * @return <Attribute> mdp
    */

    public function getMdp(){
        return $this->mdp;
    }

    /**
    * Méthode de définition de l'attribut mdp de la classe Mysql
    * @author Valentin CARRUESCO
    * @category Accesseur
    * @param <Attribute> $mdp
    * @return Aucun retour
    */

    public function setMdp($mdp){
        $this->mdp = $mdp;
    }

    /**
    * Méthode de récuperation de l'attribut bdd de la classe Mysql
    * @author Valentin CARRUESCO
    * @category Accesseur
    * @param Aucun
    * @return <Attribute> bdd
    */

    public function getBdd(){
        return $this->bdd;
    }

    /**
    * Méthode de définition de l'attribut bdd de la classe Mysql
    * @author Valentin CARRUESCO
    * @category Accesseur
    * @param <Attribute> $bdd
    * @return Aucun retour
    */

    public function setBdd($bdd){
        $this->bdd = $bdd;
    }

    /**
    * Méthode de récuperation de l'attribut port de la classe Mysql
    * @author Valentin CARRUESCO
    * @category Accesseur
    * @param Aucun
    * @return <Attribute> port
    */

    public function getPort(){
        return $this->port;
    }

    /**
    * Méthode de définition de l'attribut port de la classe Mysql
    * @author Valentin CARRUESCO
    * @category Accesseur
    * @param <Attribute> $port
    * @return Aucun retour
    */

    public function setPort($port){
        $this->port = $port;
    }
}
?>
