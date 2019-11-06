<?php

namespace CuxFramework\utils;

class Cux extends CuxSingleton {

    public $version = "1.0.0";
    public $appName = "Cux PHP Framework";
    public $author = array(
        "name" => "Mihail Cuculici",
        "email" => "mihai.cuculici@gmail.com"
    );
    public $debug = false;
    
    public $language = "en";
    
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
    
    private $_params = array();
    
    public static function translate($category, $message, $params = array()){
        
        $ref = static::getInstance();
        
        $message = $ref->messages->translate($category, $message, $ref->language);
        
        if (!empty($params)){
            foreach ($params as $key => $value){
                $message = str_replace($key, $value, $message);
            }
        }
        return $message;
    }
    
    public static function config(array $config): void {
        $ref = static::getInstance();
        $ref->startTime = microtime(true);
        $ref->loadDefaultComponents($config);
        
        if (isset($config["components"])) {
            foreach ($config["components"] as $cId => $component) {
                $ref->loadComponent($cId, $component);
            }
        }
        if (isset($config["behaviours"])){
            $ref->_behaviours = $config["behaviours"];
            $config["behaviours"] = null;
            unset($config["behaviours"]);
        }
        
        if (isset($config["params"])){
            $ref->_params = $config["params"];
            unset($config["params"]);
        }
        
        CuxBase::config($ref, $config);
        
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
    
    public function loadDefaultComponents(array $config): void{
        if (!isset($config["components"]) || !isset($config["components"]["exceptionHandler"])){
            $this->loadComponent("exceptionHandler", array(
                "class" => 'CuxFramework\components\exception\CuxExceptionHandler'
            ));
        }
        if (!isset($config["components"]) || !isset($config["components"]["logger"])){
            $this->loadComponent("logger", array(
                "class" => 'CuxFramework\components\log\CuxNullLogger'
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
        if (!isset($config["components"]) || !isset($config["components"]["urlManager"])){
            $this->loadComponent("traffic", array(
                "class" => 'CuxFramework\components\url\CuxUrlManager'
            ));
        }
        if (!isset($config["components"]) || !isset($config["components"]["layout"])){
            $this->loadComponent("layout", array(
                "class" => 'CuxFramework\components\layout\CuxLayout'
            ));
        }
        if (!isset($config["components"]) || !isset($config["components"]["user"])){
            $this->loadComponent("user", array(
                "class" => 'CuxFramework\components\user\CuxUser'
            ));
        }
    }
    
    public function run(){
        $this->beforeRun();
        
        $routeInfo = explode("/", $this->urlManager->parseRequest($this->request));
        
        $this->_moduleName = $routeInfo[0];
        
        if (!$this->moduleExists($this->_moduleName)){
            throw new \Exception("Modul invalid", 404);
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
            Cux::getInstance()->logger->log(\components\log\CuxLogger::ERROR, $ex->getMessage(), $exArray);
            throw new Exception("Controller invalid", 404);
        }
        
        $this->afterRun();
    }
    
    private function loadModule($moduleName){
        $module = ($this->isModuleRelative($moduleName)) ? $this->getFullyQualifiedModuleName($moduleName, true) : $this->getFullyQualifiedModuleName($moduleName);
        $this->_module = $module::getInstance();
    }
    
    private function getFullyQualifiedModuleName($moduleName, $relative=false){
        return ($relative) ? "modules\\".ucfirst($moduleName)."Module" : "CuxFramework\\modules\\".ucfirst($moduleName)."Module";
    }
    
    private function isModuleRelative($moduleName){
        $fullyQualifiedNameRelative = $this->getFullyQualifiedModuleName($moduleName, true);
        return (class_exists($fullyQualifiedNameRelative) && is_subclass_of($fullyQualifiedNameRelative, "CuxFramework\utils\CuxSingleton"));
    }
    
    public function moduleExists($moduleName){
        $fullyQualifiedName = $this->getFullyQualifiedModuleName($moduleName);
        $fullyQualifiedNameRelative = $this->getFullyQualifiedModuleName($moduleName, true);
        return (class_exists($fullyQualifiedName) && is_subclass_of($fullyQualifiedName, "CuxFramework\utils\CuxSingleton")) || (class_exists($fullyQualifiedNameRelative) && is_subclass_of($fullyQualifiedNameRelative, "CuxFramework\utils\CuxSingleton"));
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
        $config["class"]::config($config["params"]);
        $this->_components[$cName] = $config["class"]::getInstance();
    }

    public function hasComponent($name) {
        return isset($this->_components[$name]);
    }

    public function __get(string $name) {
        if (isset($this->_components[$name])) {
            return $this->_components[$name];
        } elseif (property_exists($this, $name)) {
            return $this->$name;
        } else {
            throw new \Exception("Clasa ".get_called_class()." nu are nicio proprietate sau componenta denumita `" . $name . "`", 503);
        }
    }

    public function basePath(): string{
        return realpath(__DIR__.DIRECTORY_SEPARATOR."../../../"); // we are in the "vendor/cuxframework/utils" folder
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
                'an',
                'ani'
            ),
            'm' => array(
                'luna',
                'luni'
            ),
            'w' => array(
                'saptamana',
                'saptamani'
            ),
            'd' => array(
                'zi',
                'zile'
            ),
            'h' => array(
                'ora',
                'ore'
            ),
            'i' => array(
                'minut',
                'minute'
            ),
            's' => array(
                'secunda',
                'secunde'
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
        
        return !empty($ret) ? ("acum ".implode(", ", $ret)) : "acum";
        
    }
    
    public function createSlug($text, $separator = '-') {

        $matrix = array(
            'й' => 'i', 'ц' => 'c', 'у' => 'u', 'к' => 'k', 'е' => 'e', 'н' => 'n',
            'г' => 'g', 'ш' => 'sh', 'щ' => 'shch', 'з' => 'z', 'х' => 'h', 'ъ' => '',
            'ф' => 'f', 'ы' => 'y', 'в' => 'v', 'а' => 'a', 'п' => 'p', 'р' => 'r',
            'о' => 'o', 'л' => 'l', 'д' => 'd', 'ж' => 'zh', 'э' => 'e', 'ё' => 'e',
            'я' => 'ya', 'ч' => 'ch', 'с' => 's', 'м' => 'm', 'и' => 'i', 'т' => 't',
            'ь' => '', 'б' => 'b', 'ю' => 'yu', 'ү' => 'u', 'қ' => 'k', 'ғ' => 'g',
            'ә' => 'e', 'ң' => 'n', 'ұ' => 'u', 'ө' => 'o', 'Һ' => 'h', 'һ' => 'h',
            'і' => 'i', 'ї' => 'ji', 'є' => 'je', 'ґ' => 'g',
            'Й' => 'I', 'Ц' => 'C', 'У' => 'U', 'Ұ' => 'U', 'Ө' => 'O', 'К' => 'K',
            'Е' => 'E', 'Н' => 'N', 'Г' => 'G', 'Ш' => 'SH', 'Ә' => 'E', 'Ң ' => 'N',
            'З' => 'Z', 'Х' => 'H', 'Ъ' => '', 'Ф' => 'F', 'Ы' => 'Y', 'В' => 'V',
            'А' => 'A', 'П' => 'P', 'Р' => 'R', 'О' => 'O', 'Л' => 'L', 'Д' => 'D',
            'Ж' => 'ZH', 'Э' => 'E', 'Ё' => 'E', 'Я' => 'YA', 'Ч' => 'CH', 'С' => 'S',
            'М' => 'M', 'И' => 'I', 'Т' => 'T', 'Ь' => '', 'Б' => 'B', 'Ю' => 'YU',
            'Ү' => 'U', 'Қ' => 'K', 'Ғ' => 'G', 'Щ' => 'SHCH', 'І' => 'I', 'Ї' => 'YI',
            'Є' => 'YE', 'Ґ' => 'G',
            'ă' => 'a', 'Ă' => 'A', 'â' => 'a', 'Â' => 'A', 'î' => 'i', 'Î' => 'I',
            'ş' => 's', 'Ş' => 'S', 'ţ' => 't', 'Ţ' => 'T', 'ș' => 's', 'Ș' => 'S',
            'ț' => 't', 'Ţ' => 'T'
        );
        foreach ($matrix as $from => $to) {
            $text = mb_eregi_replace($from, $to, $text);
        }
        
        $text = preg_replace('~[^\\pL\d]+~u', $separator, strtolower($text));
        $flip = $separator == '-' ? '_' : '-';
        $text = preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $text);
        $text = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $text);
        return substr(trim($text, $separator), 0, 64);
    }
    
    public function goBack(){
        if (($redirectLink = Cux::getInstance()->session->get("redirectLink")) !== FALSE && !is_null($redirectLink)) {
            Cux::getInstance()->session->set("redirectLink", null);
            Cux::getInstance()->redirect($redirectLink, 302);
        } else {
            Cux::getInstance()->redirect("/", 302);
        }
    }
    
}
