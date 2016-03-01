<?php

class Logger {

    CONST LOGGER_IDENTIFIER = 'logger';
    protected $name;
    protected $logs = array();

    public function __construct($name = 'messages'){
        $this->name = $name;
    }

    public function save(){
        $_SESSION[self::LOGGER_IDENTIFIER][$this->getName()] = serialize($this->logs);
    }

    public function appendLogs($msg){
        $this->logs[] = $msg;
    }

    public function loadLogs()
    {
        $logs = $this->getLogs();
        $this->destroy();
        return $logs;
    }

    protected function destroy(){
        unset($_SESSION[self::LOGGER_IDENTIFIER][$this->getName()]);
    }

    protected function isEmpty(){
        return ! isset($_SESSION[self::LOGGER_IDENTIFIER][$this->getName()])
            || empty($_SESSION[self::LOGGER_IDENTIFIER][$this->getName()]);
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setLogs($logs)
    {
        $this->logs = $logs;
    }

    public function getLogs()
    {
        if($this->isEmpty()){
            return $this->logs;
        }
        return unserialize( $_SESSION[self::LOGGER_IDENTIFIER][$this->getName()] );
    }

}

?>
