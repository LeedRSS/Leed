<?php

class Install {

    const DEFAULT_TABLES_PREFIX = 'leed__';
    const CONSTANT_FILE = 'constant.php';
    public $finished = false;
    public $logs = array(
        'errors' => array(),
        'notices' => array()
    );
    public $options = array(
        'user' => array(
            'login' => "",
            'password' => ""
        ),
        'db' => array(
            'mysqlBase' => "",
            'mysqlHost' => "",
            'mysqlLogin' => "",
            'mysqlMdp' => "",
            'mysqlPrefix' => self::DEFAULT_TABLES_PREFIX
        )
    );


    public function __construct() {
        require_once('Logger.class.php');
        $this->logger = new Logger('install');
    }

    public function overrideDefaultValues($_) {
        foreach ($this->options as $type => $options) {
            foreach ($options as $var => $defaultValue) {
                if (!empty($_[$var])) {
                    $val = $type === 'db' ? str_replace("'", "\'", $_[$var]) : Functions::secure($_[$var]);
                    $this->options[$type][$var] = $val;
                }
            }
        }
    }

    public function launch($letsGo) {
        $this->checkFunctionsExists();
        if(!$letsGo){
            return false;
        }
        $this->checkLoginPassword();
        $this->checkdb();
        if(!$this->hasErrors()) {
            $this->createConstantFile();

            require_once('constant.php');
            require_once('MysqlEntity.class.php');
            class_exists('Update') or require_once('Update.class.php');
            Update::ExecutePatch(true);
            require_once('Feed.class.php');
            require_once('Event.class.php');
            require_once('User.class.php');
            require_once('Folder.class.php');
            require_once('Configuration.class.php');

            $this->createUser();
            $configurationManager = new Configuration();
            $configurationManager->install();
            $this->setFinished(true);
            $this->logger->destroy();
        }
    }

    protected function createConstantFile() {
        $constant = "<?php
define('VERSION_NUMBER','1.6');
define('VERSION_NAME','Dev');

//Host de Mysql, le plus souvent localhost ou 127.0.0.1
define('MYSQL_HOST','{$this->options['db']['mysqlHost']}');
//Identifiant MySQL
define('MYSQL_LOGIN','{$this->options['db']['mysqlLogin']}');
//mot de passe MySQL
define('MYSQL_MDP','{$this->options['db']['mysqlMdp']}');
//Nom de la base MySQL ou se trouvera leed
define('MYSQL_BDD','{$this->options['db']['mysqlBase']}');
//Prefix des noms des tables leed pour les bases de données uniques
define('MYSQL_PREFIX','{$this->options['db']['mysqlPrefix']}');
?>";

        file_put_contents(self::CONSTANT_FILE, $constant);
        if (!is_readable(self::CONSTANT_FILE))
            die('"'.self::CONSTANT_FILE.'" not found!');
    }

    protected function createUser() {
        $userManager = new User();
        if ($userManager->tableExists()) {
            $userManager->truncate();
        }
        $userManager->create();
        $userManager->add($this->options['user']['login'], $this->options['user']['password'], $this->logger);
        $_SESSION['currentUser'] = serialize($userManager->get($this->options['user']['login']));
    }

    protected function checkLoginPassword() {
        if(
            empty($this->options['user']['password'])
            || empty($this->options['user']['login'])
        ) {
            $this->logs['errors'][] = _t('INSTALL_ERROR_USERPWD');
            return false;
        }
        return true;
    }

    protected function checkDb() {
        if(
            empty($this->options['db']['mysqlHost'])
            || empty($this->options['db']['mysqlLogin'])
            || empty($this->options['db']['mysqlMdp'])
            || empty($this->options['db']['mysqlBase'])
        ) {
            $this->logs['errors'][] = _t('INSTALL_ERROR_DB_INFOS');
            return false;
        }
        if (!Functions::testDb(
            $this->options['db']['mysqlHost'], $this->options['db']['mysqlLogin'], $this->options['db']['mysqlMdp'], $this->options['db']['mysqlBase']
        )) {
            $this->logs['errors'][] = _t('INSTALL_ERROR_CONNEXION');
        } else {
            $this->logs['notices'][] = _t('INSTALL_INFO_CONNEXION');
        }
        return true;
    }

    public function hasErrors() {
        return count($this->logs['errors']) > 0;
    }

    public function checkFunctionsExists() {
        if(!is_writable('./')){
            $this->logs['errors'][] = _t('INSTALL_ERROR_RIGHT', array(str_replace(basename(__FILE__),'',__FILE__)));
        }else{
            $this->logs['notices'][] = _t('INSTALL_INFO_RIGHT');
        }
        if (!@function_exists('mysqli_connect')){
            $this->logs['errors'][] = _t('INSTALL_ERROR_MYSQLICONNECT');
        }else{
            $this->logs['notices'][] = _t('INSTALL_INFO_MYSQLICONNECT');
        }
        if (!@function_exists('file_get_contents')){
            $this->logs['errors'][] =  _t('INSTALL_ERROR_FILEGET');
        }else{
            $this->logs['notices'][] = _t('INSTALL_INFO_FILEGET');
        }
        if (!@function_exists('file_put_contents')){
            $this->logs['errors'][] = _t('INSTALL_ERROR_FILEPUT');
        }else{
            $this->logs['notices'][] = _t('INSTALL_INFO_FILEPUT');
        }
        if (!@function_exists('curl_exec')){
            $this->logs['errors'][] = _t('INSTALL_ERROR_CURL');
        }else{
            $this->logs['notices'][] = _t('INSTALL_INFO_CURL');
        }
        if (@version_compare(PHP_VERSION, '5.1.0') <= 0){
            $this->logs['errors'][] = _t('INSTALL_ERROR_PHPV', array(PHP_VERSION));
        }else{
            $this->logs['notices'][] = _t('INSTALL_INFO_PHPV', array(PHP_VERSION));
        }
        if(ini_get('safe_mode') && ini_get('max_execution_time')!=0){
            $this->logs['errors'][] = _t('INSTALL_ERROR_SAFEMODE');
        }else{
            $this->logs['notices'][] = _t('INSTALL_INFO_SAFEMODE');
        }
    }

    public function setFinished($finished) {
        $this->finished = $finished;

    }

    public function getFinished() {
        return $this->finished;
    }
}
