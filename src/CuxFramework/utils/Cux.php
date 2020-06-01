<?php

namespace CuxFramework\utils;

use CuxFramework\components\log\CuxLogger;

class Cux extends CuxSingleton {

    public $version = "1.0.0";
    public $appName = "Cux PHP Framework";
    public $encryptionSalt = "klM1$%#@!F@#N.:]"; // random 16bytes salt. This should really be changed from the config file
    public $author = array(
        "name" => "Mihail Cuculici",
        "email" => "mihai.cuculici@gmail.com"
    );
    public $debug = false;
    
    public $language = "en";
    
    public $charset = "UTF-8";
    
    public $events = array();
    
    private $_components = array();
    
    private $_behaviours = array();

    private $startTime;
    private $endTime;
    
    private $_module;
    private $_controller;
    private $_action;
    private $_moduleName;
    private $_controllerName;
    private $_actionName;
    
    private $_console = false;
    
    private $_params = array();
    
    public function raiseEvent($eventName, $params = array()){
        if (isset($this->events[$eventName])){
            $this->events[$eventName]($params);
        }
    }
    
    public static function translate($category, $message, $params = array(), $context = ""){
        
        $ref = static::getInstance();
        
        if ($ref->hasComponent("messages")){
            $message = $ref->messages->translate($category, $message, $ref->language, $context);
        }
        
        if (!empty($params)){
            foreach ($params as $key => $value){
                $message = str_replace($key, $value, $message);
            }
        }
        return $message;
    }
    
    public static function emergency($message, array $context = array()): bool {
        return static::log(CuxLogger::EMERGENCY, $message, $context);
    }
    
    public static function alert($message, array $context = array()): bool {
        return static::log(CuxLogger::ALERT, $message, $context);
    }
    
    public static function critical($message, array $context = array()): bool {
        return static::log(CuxLogger::CRITICAL, $message, $context);
    }
    
    public static function error($message, array $context = array()): bool {
        return static::log(CuxLogger::ERROR, $message, $context);
    }
    
    public static function warning($message, array $context = array()): bool {
        return static::log(CuxLogger::WARNING, $message, $context);
    }
    
    public static function notice($message, array $context = array()): bool {
        return static::log(CuxLogger::NOTICE, $message, $context);
    }
    
    public static function info($message, array $context = array()): bool {
        return static::log(CuxLogger::INFO, $message, $context);
    }
    
    public static function debug($message, array $context = array()): bool {
        return static::log(CuxLogger::DEBUG, $message, $context);
    }
    
    public static function log(int $level, string $message, array $context = array()): bool{
        $ret = true;
    
        $ref = static::getInstance();
        
        if ($ref->hasComponent("logger")){
            foreach ($ref->logger as $logger){
                $ret = $ret && $logger->log($level, $message, $context);
            }
        }
        
        return $ret;
    }
    
    public static function config(array $config) {
        $ref = static::getInstance();
        $ref->startTime = microtime(true);
        
        if (isset($config["behaviours"])){
            $ref->_behaviours = $config["behaviours"];
            $config["behaviours"] = null;
            unset($config["behaviours"]);
        }
        
        if (isset($config["params"])){
            $ref->_params = $config["params"];
            unset($config["params"]);
        }
        
        $ref->_console = (php_sapi_name() === 'cli');
        
        CuxBase::config($ref, $config);
        
        $ref->loadDefaultComponents($config);
        
        if (isset($config["components"])) {
            foreach ($config["components"] as $cId => $component) {
                $ref->loadComponent($cId, $component);
            }
        }
        
    }
    
    public function isConsoleApp(){
        return $this->_console;
    }
    
    public function isWebApp(){
        return !$this->_console;
    }
    
    public function getParams(){
        return $this->_params;
    }
    
    public function getParam(string $paramName){
        return isset($this->_params[$paramName]) ? $this->_params[$paramName] : null;
    }
    
    public function poweredBy(): string {
        $ret = "Cux PHP Framework";
        if ($this->version){
            $ret .= " v.".$this->version;
        }
        return $ret;
    }
    
    public function copyright(): string {
        return "<a href='mailto:".$this->author["email"]."'>".$this->author["name"]."</a> ".date("Y");
    }
    
    public function loadDefaultComponents(array $config){
        if (!isset($config["components"]) || !isset($config["components"]["exceptionHandler"])){
            $this->loadComponent("exceptionHandler", array(
                "class" => 'CuxFramework\components\exception\CuxExceptionHandler'
            ));
        }
        if (!isset($config["components"]) || !isset($config["components"]["logger"])){
            $this->loadComponent("logger", array(
                "class" => 'CuxFramework\components\log\CuxFileLogger'
            ));
        }
        if (!isset($config["components"]) || !isset($config["components"]["messages"])){
            $this->loadComponent("messages", array(
                "class" => 'CuxFramework\components\messages\CuxFileMessages'
            ));
        }
        if (!isset($config["components"]) || !isset($config["components"]["traffic"])){
            $this->loadComponent("traffic", array(
                "class" => 'CuxFramework\components\traffic\CuxNullTraffic'
            ));
        }
        if ($this->isWebApp()){
            if (!isset($config["components"]) || !isset($config["components"]["request"])){
                $this->loadComponent("request", array(
                    "class" => 'CuxFramework\components\request\CuxRequest'
                ));
            }
            if (!isset($config["components"]) || !isset($config["components"]["urlManager"])){
                $this->loadComponent("traffic", array(
                    "class" => 'CuxFramework\components\url\CuxUrlManager'
                ));
            }
            if (!isset($config["components"]) || !isset($config["components"]["clientScript"])){
                $this->loadComponent("clientScript", array(
                    "class" => 'CuxFramework\components\clientScript\CuxClientScript'
                ));
            }
            if (!isset($config["components"]) || !isset($config["components"]["layout"])){
                $this->loadComponent("layout", array(
                    "class" => 'CuxFramework\components\layout\CuxLayout'
                ));
            }
        }
        
        if (!isset($config["components"]) || !isset($config["components"]["user"])){
            $this->loadComponent("user", array(
                "class" => 'CuxFramework\components\user\CuxUser'
            ));
        }
    }
    
    public function run(){
        $this->beforeRun();
        
        $route = $this->urlManager->getMatchedRoute();
        
        if (!$route){
            throw new \Exception(Cux::translate("core.errors", "Invalid URL", array(), "Message shown on PageNotFound exception"), 404);
        }
        
        $routeDetails = $route->getDetails();
        $routePath = $routeDetails["path"];
        if (strpos($routePath, "/") === 0){ // remove leading slash ( if necessary)
            $routePath = substr($routePath, 1);
        }
        $routeInfo = explode("/", $routePath);
        
        $this->_moduleName = $routeInfo[0];
        
        if (!$this->moduleExists($this->_moduleName)){
            throw new \Exception(Cux::translate("core.errors", "Invalid module", array(), "Message shown on PageNotFound exception"), 404);
        }
        else{
            $this->loadModule($this->_moduleName);
            $this->_controllerName = isset($routeInfo[1]) ? $routeInfo[1] : $this->_module->defaultController;
        }
        
        try{
            $this->_module->loadController($this->_controllerName);
            $this->_controller = $this->_module->getController();
            $this->_actionName = isset($routeInfo[2]) ? $routeInfo[2] : $this->_controller->defaultAction;
            $this->_controller->loadAction($this->_actionName);
            $this->_action = $this->_controller->getAction();
            
            $this->layout->setModuleName($this->_moduleName);
            $this->layout->setControllerName($this->_controllerName);
            
            $this->_module->run();
        } catch (Exception $ex) {
            $exArray = array(
                "code" => $ex->getCode(),
                "message" => $ex->getMessage(),
                "stackTrace" => $ex->getTrace()
            );
//            Cux::log(\components\log\CuxLogger::ERROR, $ex->getMessage(), $exArray);
            throw new \Exception(Cux::translate("core.errors", "Invalid controller", array(), "Message shown on PageNotFound exception"), 404);
        }
        
        $this->afterRun();
    }
    
    private function loadModule($moduleName){
        $module = ($this->isModuleRelative($moduleName)) ? $this->getFullyQualifiedModuleName($moduleName, true) : $this->getFullyQualifiedModuleName($moduleName);
        $moduleInstance = new $module();
        $this->_module = $moduleInstance;
    }
    
    private function getFullyQualifiedModuleName($moduleName, $relative=false){
        return ($relative) ? "modules\\".$moduleName."\\".ucfirst($moduleName)."Module" : "CuxFramework\\modules\\".$moduleName."\\".ucfirst($moduleName)."Module";
    }
    
    private function isModuleRelative($moduleName){
        $fullyQualifiedNameRelative = $this->getFullyQualifiedModuleName($moduleName, true);
        return (class_exists($fullyQualifiedNameRelative) && is_subclass_of($fullyQualifiedNameRelative, "CuxFramework\utils\CuxBaseObject"));
    }
    
    public function moduleExists($moduleName){
        $fullyQualifiedName = $this->getFullyQualifiedModuleName($moduleName);
        $fullyQualifiedNameRelative = $this->getFullyQualifiedModuleName($moduleName, true);
        return (class_exists($fullyQualifiedName) && is_subclass_of($fullyQualifiedName, "CuxFramework\utils\CuxBaseObject")) || (class_exists($fullyQualifiedNameRelative) && is_subclass_of($fullyQualifiedNameRelative, "CuxFramework\utils\CuxBaseObject"));
    }
    
    public function beforeRun(){
        if (isset($this->_behaviours["beforeRun"]) && !empty($this->_behaviours["beforeRun"])){
            $this->_behaviours["beforeRun"]($this->startTime);
        }
    }

    public function afterRun(){
        $this->endTime = microtime(true);
        if (isset($this->_behaviours["afterRun"]) && !empty($this->_behaviours["afterRun"])){
            $this->_behaviours["afterRun"]($this->endTime);
        }
    }
    
    public function loadComponent($cName, $config) {
        if (!isset($config["params"])){
            $config["params"] = array();
        }
        if (isset($config["instances"])){
            $this->_components[$cName] = array();
            foreach ($config["instances"] as $config2){
                $instance = new $config2["class"]();
                $instance->config($config2["params"]);
                $this->_components[$cName][] = $instance;
            }
        } else {
            $instance = new $config["class"]();
            $instance->config($config["params"]);
            $this->_components[$cName] = $instance;
        }
    }

    public function hasComponent($name) {
        return isset($this->_components[$name]);
    }
    
    public function getModule(): \CuxFramework\modules\cuxDefault\CuxDefaultModule{
        return $this->_module;
    }
    
    public function getController(): \CuxFramework\modules\cuxDefault\controllers\CuxDefaultController{
        return $this->_controller;
    }
    
    public function getAction(): string{
        return $this->_action;
    }
    
    public function getActionName(): string{
        return $this->_actionName;
    }
    
    public function getActionParams(): array{
        $controller = $this->getController();
        if ($controller){
            return $controller->getParams($this->getActionName());
        }
        return array();
    }

    public function __get(string $name) {
        if (isset($this->_components[$name])) {
            return $this->_components[$name];
        } elseif (property_exists($this, $name)) {
            return $this->$name;
        } else {
            $className = get_class($this);
            throw new \Exception(Cux::translate("core.errors", "Undefined property: {class}.{attribute}", array("{class}" => $className, "{attribute}" => $name), "Message shown when trying to access invalid class properties"), 503);
        }
    }

    public function basePath(): string{
        return realpath(__DIR__.DIRECTORY_SEPARATOR."../../../"); // we are in the "vendor/mihaicux/cuxframework/src/CuxFramework/utils" folder
//        return realpath("");
    }
    
    public function redirect(string $location, int $status=302){
        header("Location: $location", true, $status);
        exit();
    }
 
    public function timeEllapsed($dateTime, $full = true): string{
        
        $ret = array();
        
        $crt  = new \DateTime();
        $prec = new \DateTime($dateTime);
        
        $diff = $crt->diff($prec);
        
        $map = array(
            'y' => array(
                Cux::translate("core.debug", "year", array(), "Core message, used for writing execution time"),
                Cux::translate("core.debug", "years", array(), "Core message, used for writing execution time")
            ),
            'm' => array(
                Cux::translate("core.debug", "month", array(), "Core message, used for writing execution time"),
                Cux::translate("core.debug", "months", array(), "Core message, used for writing execution time")
            ),
            'w' => array(
                Cux::translate("core.debug", "week", array(), "Core message, used for writing execution time"),
                Cux::translate("core.debug", "weeks", array(), "Core message, used for writing execution time")
            ),
            'd' => array(
                Cux::translate("core.debug", "day", array(), "Core message, used for writing execution time"),
                Cux::translate("core.debug", "days", array(), "Core message, used for writing execution time")
            ),
            'h' => array(
                Cux::translate("core.debug", "hour", array(), "Core message, used for writing execution time"),
                Cux::translate("core.debug", "hours", array(), "Core message, used for writing execution time")
            ),
            'i' => array(
                Cux::translate("core.debug", "minute", array(), "Core message, used for writing execution time"),
                Cux::translate("core.debug", "minutes", array(), "Core message, used for writing execution time")
            ),
            's' => array(
                Cux::translate("core.debug", "second", array(), "Core message, used for writing execution time"),
                Cux::translate("core.debug", "seconds", array(), "Core message, used for writing execution time")
            )
        );
        
        foreach ($map as $key => $vals){
            if ($diff->$key > 0){
                $ret[] = $diff->$key." ".(($diff->$key == 1) ? $vals[0] : $vals[1]);
            }
        }
        
        if (!$full && !empty($ret)){
            $ret = array_slice($ret, 0, 1);
        }
        
//        return !empty($ret) ? Cux::translate("core.debug", "{timeEllapsed} ago", array("{timeEllapsed}" => implode(", ", $ret)), "Core message, used for writing execution time") : Cux::translate("core.debug", "A few seconds ago", array(), "Core message, used for writing execution time");
        return !empty($ret) ? implode(", ", $ret) : "";
        
    }
    
    public function goBack(){
        if (($redirectLink = Cux::getInstance()->session->get("redirectLink")) !== FALSE && !is_null($redirectLink)) {
            Cux::getInstance()->session->set("redirectLink", null);
            Cux::getInstance()->redirect($redirectLink, 302);
        } else {
            Cux::getInstance()->redirect("/", 302);
        }
    }
    
    /**
     * Get the relative path for the framework
     * Should return "vendor/mihaicux/cuxframework/src/CuxFramework/"
     * @return string
     */
    public static function getFrameworkPath(): string{
        return "vendor".DIRECTORY_SEPARATOR."mihaicux".DIRECTORY_SEPARATOR."cuxframework".DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."CuxFramework".DIRECTORY_SEPARATOR;
    }
    
}
