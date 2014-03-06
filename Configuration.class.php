<?php


/*
 @nom: Configuration
 @auteur: Idleman (idleman@idleman.fr)
 @description: Classe de gestion des préférences, fonctionne sur un simple système clé=>valeur avec un cache session pour eviter les requête inutiles
 */

class Configuration extends MysqlEntity{

    protected $id,$key,$value,$confTab;
    protected $TABLE_NAME = 'configuration';
    protected $CLASS_NAME = 'Configuration';
    protected $object_fields =
    array(
        'id'=>'key',
        'key'=>'longstring',
        'value'=>'longstring'
    );

    function __construct(){
        parent::__construct();
    }

    public function getAll(){

        if(!isset($_SESSION['configuration'])){

        $configurationManager = new Configuration();
        $configs = $configurationManager->populate();
        $confTab = array();

        foreach($configs as $config){
            $this->confTab[$config->getKey()] = $config->getValue();
        }

        $_SESSION['configuration'] = serialize($this->confTab);

        }else{
            $this->confTab = unserialize($_SESSION['configuration']);
        }
    }

    public function get($key){

        return (isset($this->confTab[$key])?$this->confTab[$key]:'');
    }

    public function put($key,$value){
        $configurationManager = new Configuration();
        if (isset($this->confTab[$key])){
            $configurationManager->change(array('value'=>$value),array('key'=>$key));
        } else {
            $configurationManager->add($key,$value);
        }
        $this->confTab[$key] = $value;
        unset($_SESSION['configuration']);
    }

    public function add($key,$value){
        $config = new Configuration();
        $config->setKey($key);
        $config->setValue($value);
        $config->save();
        $this->confTab[$key] = $value;
        unset($_SESSION['configuration']);
    }

    function getId(){
        return $this->id;
    }

    function getKey(){
        return $this->key;
    }

    function setKey($key){
        $this->key = $key;
    }

    function getValue(){
        return $this->value;
    }

    function setValue($value){
        $this->value = $value;
    }




}

?>