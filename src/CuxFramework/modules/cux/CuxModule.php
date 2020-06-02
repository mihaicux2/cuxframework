<?php

namespace CuxFramework\modules\cux;

use CuxFramework\utils\CuxBaseObject;
use CuxFramework\utils\Cux;

class CuxModule extends CuxBaseObject{
    
    protected $_name;
    protected $_controller;
    
    public $defaultController = "cuxDefault";
    
    public function config(array $config) {
        parent::config($config);
    }

    public function run(){
        $this->_controller->run();
    }
    
    public function getName(){
        return lcfirst(substr((new \ReflectionClass($this))->getShortName(), 0, -6));
    }
    
    public function loadController($controllerName){
        if (!$this->controllerExists($controllerName)){
            throw new \Exception(Cux::translate("core.errors", "Invalid controller", array(), "Message shown on PageNotFound exception"), 404);
        }
        else{
            $controller = ($this->isControllerRelative($controllerName)) ? $this->getFullyQualifiedControllerName($controllerName, true) : $this->getFullyQualifiedControllerName($controllerName);
            $controllerInstance = new $controller();
            $this->_controller = $controllerInstance;
        }
    }
    
    public function loadAction($actionName){
        try{
            $this->_controller->loadAction($actionName);
        } catch (\Exception $ex) {
            throw new \Exception(Cux::translate("core.errors", "Invalid action", array(), "Message shown on PageNotFound exception"), 404);
        }
    }
    
    public function controllerExists($controllerName){
        $fullyQualifiedName = $this->getFullyQualifiedControllerName($controllerName);
        $fullyQualifiedNameRelative = $this->getFullyQualifiedControllerName($controllerName, true);
        
        return (class_exists($fullyQualifiedName) && is_subclass_of($fullyQualifiedName, "CuxFramework\utils\CuxBaseObject")) || (class_exists($fullyQualifiedNameRelative) && is_subclass_of($fullyQualifiedNameRelative, "CuxFramework\utils\CuxBaseObject"));
    }
    
    private function getFullyQualifiedControllerName($controllerName, $relative=false){
        return ($relative) ? "modules\\".$this->getName()."\\controllers\\".ucfirst($controllerName)."Controller" : "CuxFramework\\modules\\".$this->getName()."\\controllers\\".ucfirst($controllerName)."Controller";
    }
    
    private function isControllerRelative($controllerName){
        $fullyQualifiedNameRelative = $this->getFullyQualifiedControllerName($controllerName, true);
        return (class_exists($fullyQualifiedNameRelative) && is_subclass_of($fullyQualifiedNameRelative, "CuxFramework\utils\CuxBaseObject"));
    }
    
    public function getController(){
        return $this->_controller;
    }
    
}

