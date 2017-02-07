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

    public function flushLogs()
    {
        $logs = $this->getLogs();
        $this->destroy();
        return $logs;
    }

    public function destroy(){
        unset($_SESSION[self::LOGGER_IDENTIFIER][$this->getName()]);
    }

    protected function mergeLogsInSession(){
        if( isset($_SESSION[self::LOGGER_IDENTIFIER][$this->getName()])
            || !empty($_SESSION[self::LOGGER_IDENTIFIER][$this->getName()])
        ) {
            $this->logs = array_unique(array_merge($this->logs, unserialize($_SESSION[self::LOGGER_IDENTIFIER][$this->getName()])));
        }
    }

    public function hasLogs()
    {
        return count($this->logs) > 0;
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
        $this->mergeLogsInSession();
        return $this->logs;
    }

}

?>
