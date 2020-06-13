<?php

/**
 * Framework core component file
 * 
 * @package Utils
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\utils;

use CuxFramework\components\log\CuxLogger;

/**
 * This is the framework's main class and it's core component.
 */
class Cux extends CuxSingleton {

    /**
     * The framework version
     * @var string
     */
    public $version = "1.0.0";
    
    /**
     * Running app name
     * @var string
     */
    public $appName = "Cux PHP Framework";
    
    /**
     * Salt to be used by the encryption components
     * @var string
     */
    public $encryptionSalt = "klM1$%#@!F@#N.:]"; // random 16bytes salt. This should really be changed from the config file
    
    /**
     * The app author
     * @var array
     */
    public $author = array(
        "name" => "Mihail Cuculici",
        "email" => "mihai.cuculici@gmail.com"
    );
    
    /**
     * Set to true for debugging purposes. Disable debug for the production environment
     * @var bool
     */
    public $debug = false;
    
    /**
     * The default app language
     * @var string
     */
    public $language = "en";
    
    /**
     * The default app HTML charset
     * @var string
     */
    public $charset = "UTF-8";
    
    /**
     * A list of callbacks that can be used during  run-time
     * Ex:
     * .....
     * "missingTranslation" => function($params){
     *     echo "Translation missing: ";
     *     print_r($params);
     * }
     * @var array
     */
    public $events = array();
    
    /**
     * The list of components to be used by the framework.
     * The list should include (and auto-loads if missing) components like "cache", "messages", "session", etc.
     * @var array
     */
    private $_components = array();
    
    /**
     * A list of callbacks that can be used before and after the actual run-time.
     * Each of these functions receives the current date-time, in microseconds
     * Ex:
     * "behaviours" => array(
     *     "beforeRun" => function($t){
     *          $micro = sprintf("%06d", ($t - floor($t)) * 1000000);
     *          $d = new \DateTime(date('Y-m-d H:i:s.' . $micro, $t));
     *          echo "Start time:".$d->format("Y-m-d H:i:s.u");
     *      },
     *     "afterRun" => function($t){
     *          $micro = sprintf("%06d", ($t - floor($t)) * 1000000);
     *          $d = new \DateTime(date('Y-m-d H:i:s.' . $micro, $t));
     *          echo "End time:".$d->format("Y-m-d H:i:s.u");
     *     }
     * @var array
     */
    private $_behaviours = array();

    /**
     * The request start time in microseconds
     * @var int
     */
    private $startTime;
    
    /**
     * The request end time in microseconds
     * @var int
     */
    private $endTime;
    
    /**
     * The current running module
     * @var \CuxFramework\modules\cux\CuxModule
     */
    private $_module;
    
    /**
     * The current running controller
     * @var \CuxFramework\modules\cux\controllers\CuxController 
     */
    private $_controller;
    
    /**
     * The curent action to be run by the current controller
     * @var string
     */
    private $_action;
    
    /**
     * The (short) name of the current module
     * @var string 
     */
    private $_moduleName;
    
    /**
     * The (short) name of the current controller
     * @var string
     */
    private $_controllerName;
    
    /**
     * The (short) name of the current action
     * @var string
     */
    private $_actionName;
    
    /**
     * Checks if the app runs in Web or CLI mode
     * @var bool 
     */
    private $_console = false;
    
    /**
     * The list of application parameters ( to be used throughout the application )
     * @var array
     */
    private $_params = array();
    
    /**
     * Handler for the predefined $_events list
     * @param string $eventName
     * @param array $params
     */
    public function raiseEvent($eventName, $params = array()){
        if (isset($this->events[$eventName])){
            $this->events[$eventName]($params);
        }
    }
    
    /**
     * Translates a given message using the "messages" component
     * Ex:
     *    Cux::translate("myCategory", "This text is translated by {user}", array("{user}" => "MihaiCux", "This text appears on the homepage");
     * @param string $category The text's category
     * @param string $message The message to be translated
     * @param array $params The list of parameters for the text to be translated
     * @param string $context
     * @return string
     */
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
    
    /**
     * Log messages with EMERGENCY level
     * @param string $message
     * @param array $context
     * @return bool
     */
    public static function emergency($message, array $context = array()): bool {
        return static::log(CuxLogger::EMERGENCY, $message, $context);
    }
    
    /**
     * Log messages with ALERT level
     * @param string $message
     * @param array $context
     * @return bool
     */
    public static function alert($message, array $context = array()): bool {
        return static::log(CuxLogger::ALERT, $message, $context);
    }
    
    /**
     * Log messages with CRITICAL level
     * @param string $message
     * @param array $context
     * @return bool
     */
    public static function critical($message, array $context = array()): bool {
        return static::log(CuxLogger::CRITICAL, $message, $context);
    }
    
    /**
     * Log messages with ERROR level
     * @param string $message
     * @param array $context
     * @return bool
     */
    public static function error($message, array $context = array()): bool {
        return static::log(CuxLogger::ERROR, $message, $context);
    }
    
    /**
     * Log messages with WARNING level
     * @param string $message
     * @param array $context
     * @return bool
     */
    public static function warning($message, array $context = array()): bool {
        return static::log(CuxLogger::WARNING, $message, $context);
    }
    
    /**
     * Log messages with NOTICE level
     * @param string $message
     * @param array $context
     * @return bool
     */
    public static function notice($message, array $context = array()): bool {
        return static::log(CuxLogger::NOTICE, $message, $context);
    }
    
    /**
     * Log messages with INFO level
     * @param string $message
     * @param array $context
     * @return bool
     */
    public static function info($message, array $context = array()): bool {
        return static::log(CuxLogger::INFO, $message, $context);
    }
    
    /**
     * Log messages with DEBUG level
     * @param string $message
     * @param array $context
     * @return bool
     */
    public static function debug($message, array $context = array()): bool {
        return static::log(CuxLogger::DEBUG, $message, $context);
    }
    
    /**
     * Logs messages with given level, using the defined "logger" components
     * @param int $level
     * @param string $message
     * @param array $context
     * @return bool
     */
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
    
    /**
     * Setup method for the framework's main component
     * @param array $config
     */
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
    
    /**
     * Returns true if the application runs in CLI mode ( from the console )
     * @return bool
     */
    public function isConsoleApp(): bool{
        return $this->_console;
    }
    
    /**
     * Returns true if the application runs under a web server
     * @return bool
     */
    public function isWebApp(): bool{
        return !$this->_console;
    }
    
    /**
     * Returns the list of application parameters
     * @return array
     */
    public function getParams(): array{
        return $this->_params;
    }
    
    /**
     * Returns a certain parameter the list of applications parameter
     * @param string $paramName
     * @return mixed
     */
    public function getParam(string $paramName){
        return isset($this->_params[$paramName]) ? $this->_params[$paramName] : null;
    }
    
    /**
     * Just for fun :)
     * @return string
     */
    public function poweredBy(): string {
        $ret = "Cux PHP Framework";
        if ($this->version){
            $ret .= " v.".$this->version;
        }
        return $ret;
    }
    
    /**
     * Just for fun
     * @return string
     */
    public function copyright(): string {
        return "<a href='mailto:".$this->author["email"]."'>".$this->author["name"]."</a> ".date("Y");
    }
    
    /**
     * If missing from the application configuration, this method loads the default core components
     * @param array $config
     */
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
        if ($this->isWebApp()){
            if (!isset($config["components"]) || !isset($config["components"]["traffic"])){
            $this->loadComponent("traffic", array(
                "class" => 'CuxFramework\components\traffic\CuxNullTraffic'
            ));
        }
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
        if (!isset($config["components"]) || !isset($config["components"]["session"])){
            $this->loadComponent("session", array(
                "class" => 'CuxFramework\components\session\CuxFileSession'
            ));
        }
    }
    
    /**
     * Application's main function. This loads the MVC login and executes the matched Module->Controller->Action
     * @throws \Exception
     */
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
    
    /**
     * Loads the defined module in the $_module property
     * @param string $moduleName
     */
    private function loadModule(string $moduleName){
        $module = ($this->isModuleRelative($moduleName)) ? $this->getFullyQualifiedModuleName($moduleName, true) : $this->getFullyQualifiedModuleName($moduleName);
        $moduleInstance = new $module();
        $this->_module = $moduleInstance;
    }
    
    /**
     * Returns the (namespace) location  for a given module
     * @param string $moduleName
     * @param bool $relative
     * @return string
     */
    private function getFullyQualifiedModuleName(string $moduleName, bool $relative=false): string{
        return ($relative) ? "modules\\".$moduleName."\\".ucfirst($moduleName)."Module" : "CuxFramework\\modules\\".$moduleName."\\".ucfirst($moduleName)."Module";
    }
    
    /**
     * Checks if a module is application-dependent or part of the framework's core
     * @param string $moduleName
     * @return bool
     */
    private function isModuleRelative(string $moduleName): bool{
        $fullyQualifiedNameRelative = $this->getFullyQualifiedModuleName($moduleName, true);
        return (class_exists($fullyQualifiedNameRelative) && is_subclass_of($fullyQualifiedNameRelative, "CuxFramework\utils\CuxBaseObject"));
    }
    
    /**
     * Checks if a given module exista and can be loaded
     * @param string $moduleName
     * @return bool
     */
    public function moduleExists($moduleName): bool{
        $fullyQualifiedName = $this->getFullyQualifiedModuleName($moduleName);
        $fullyQualifiedNameRelative = $this->getFullyQualifiedModuleName($moduleName, true);
        return (class_exists($fullyQualifiedName) && is_subclass_of($fullyQualifiedName, "CuxFramework\utils\CuxBaseObject")) || (class_exists($fullyQualifiedNameRelative) && is_subclass_of($fullyQualifiedNameRelative, "CuxFramework\utils\CuxBaseObject"));
    }
    
    /**
     * Method executed before the MVC logic.
     * Calls/executes the "beforeRun" behavior
     */
    public function beforeRun(){
        if (isset($this->_behaviours["beforeRun"]) && !empty($this->_behaviours["beforeRun"])){
            $this->_behaviours["beforeRun"]($this->startTime);
        }
    }

    /**
     * Method executed after the MVC logic
     * Calls/executes the "afterRun" behavior
     */
    public function afterRun(){
        $this->endTime = microtime(true);
        if (isset($this->_behaviours["afterRun"]) && !empty($this->_behaviours["afterRun"])){
            $this->_behaviours["afterRun"]($this->endTime);
        }
    }
    
    /**
     * Loads a given class as a framework component
     * @param string $cName
     * @param array $config
     */
    public function loadComponent(string $cName, array $config = array()) {
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

    /**
     * Checks whether a given component is loaded in the framework's running instance
     * @param string $name
     * @return bool
     */
    public function hasComponent(string $name): bool {
        return isset($this->_components[$name]);
    }
    
    /**
     * Gets the current loaded MVC module
     * @return \CuxFramework\modules\cux\CuxModule
     */
    public function getModule(): \CuxFramework\modules\cux\CuxModule{
        return $this->_module;
    }
    
    /**
     * Gets the current loaded MVC controller
     * @return \CuxFramework\modules\cux\controllers\CuxController
     */
    public function getController(): \CuxFramework\modules\cux\controllers\CuxController{
        return $this->_controller;
    }
    
    /**
     * Gets the current loaded MVC action (with the "action" prefix)
     * @return string
     */
    public function getAction(): string{
        return $this->_action;
    }
    
    /**
     * Gets the current loaded MVC action (without the "action" prefix)
     * @return string
     */
    public function getActionName(): string{
        return $this->_actionName;
    }
    
    /**
     * Gets the list of parameters to that will be added to the current MVC action
     * @return array
     */
    public function getActionParams(): array{
        $controller = $this->getController();
        if ($controller){
            return $controller->getParams($this->getActionName());
        }
        return array();
    }

    /**
     * Magic getter for loaded components
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
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

    /**
     * Get the framework's relative path
     * @return string
     */
    public function basePath(): string{
        return realpath(__DIR__.DIRECTORY_SEPARATOR."../../../"); // we are in the "vendor/mihaicux/cuxframework/src/CuxFramework/utils" folder
//        return realpath("");
    }
    
    /**
     * HTTP header redirection
     * @param string $location
     * @param int $status
     */
    public function redirect(string $location, int $status=302){
        header("Location: $location", true, $status);
        exit();
    }
 
    /**
     * Calculates the elapsed time based on a given timestamp
     * @param string $dateTime
     * @param bool $full
     * @return string
     */
    public function timeEllapsed(string $dateTime, $full = true): string{
        
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
    
    /**
     * HTTP header redirection to the previous page.
     * Redirects to homepage ("/") if no previous page is found
     */
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
