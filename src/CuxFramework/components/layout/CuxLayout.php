<?php

namespace CuxFramework\components\layout;

use CuxFramework\utils\CuxBaseObject;
use CuxFramework\utils\CuxBase;
use CuxFramework\utils\Cux;

use Composer;

class CuxLayout extends CuxBaseObject {

    private $_moduleName;
    private $_controllerName;
    public $viewsFolder = "views";
    public $layoutName = "main";
    public $layoutsFolder = "layouts";
    public $viewExtension = ".php";
    public $pageTitle = "";

    public function config(array $config) {
        parent::config($config);
    }

    public function setModuleName(string $moduleName): CuxLayout {
        $this->_moduleName = $moduleName;
        return $this;
    }

    public function setControllerName(string $controllerName): CuxLayout {
        $this->_controllerName = $controllerName;
        return $this;
    }

    public function setPageTitle(string $title): CuxLayout {
        $appName = Cux::getInstance()->appName;
        if ($appName) {
            $this->pageTitle = $appName . " :: " . $title;
        } else {
            $this->pageTitle = $title;
        }
        return $this;
    }

    public function setLayout(string $layoutName): CuxLayout {
        $this->layoutName = $layoutName;
        return $this;
    }

    private function getAbsoluteViewPath(string $viewName): string{
        return $this->viewsFolder.DIRECTORY_SEPARATOR.$viewName.$this->viewExtension;
    }
    
    
    private function getModuleRelativeViewPath(string $viewName): string{ // return modules/$module/$views/$controller/$viewName.$viewExtension
        $path = "";
        if ($this->_moduleName){
            $path .= "modules".DIRECTORY_SEPARATOR.$this->_moduleName.DIRECTORY_SEPARATOR;
        }
        $path .= $this->viewsFolder.DIRECTORY_SEPARATOR;
        if ($this->_controllerName){
            $path .= $this->_controllerName.DIRECTORY_SEPARATOR; 
        }
        $path .= $viewName.$this->viewExtension;
        
        return $path;
    }
    
    private function getAppRelativePath(string $viewName): string{ // return views/$module/$controller/$viewName.$viewExtension
        $path = $this->viewsFolder.DIRECTORY_SEPARATOR;
        if ($this->_moduleName){
            $path .= $this->_moduleName.DIRECTORY_SEPARATOR;
        }
        if ($this->_controllerName){
            $path .= $this->_controllerName.DIRECTORY_SEPARATOR; 
        }
        $path .= $viewName.$this->viewExtension;
        
        return $path;
    }
    
    public function render(string $name, array $params = array()): string {
        $layoutPath = $this->viewsFolder . DIRECTORY_SEPARATOR . $this->layoutsFolder . DIRECTORY_SEPARATOR . $this->layoutName . $this->viewExtension;
        $content = $this->renderPartial($name, $params);
        $pageTitle = $this->pageTitle;
        ob_start();
        if (file_exists($layoutPath) && is_readable($layoutPath)){
            if (!@include($layoutPath)) {
                throw new \Exception(Cux::translate("error", "Layout not found: {layout}", array(
                    "{layout}" => $this->layoutName
                )), 501);
            }
        } else {
            $layoutPath = "vendor/mihaicux/cuxframework/src/CuxFramework/".$layoutPath;
            if (!@include($layoutPath)) {
                throw new \Exception(Cux::translate("error", "Layout not found: {layout}", array(
                    "{layout}" => $this->layoutName
                )), 501);
            }
        }
        return ob_get_clean();
    }

    public function renderPartial(string $viewName, array $params = array()): string {
        
        $possiblePaths = array();
        
        $frameworkPath = "vendor".DIRECTORY_SEPARATOR."mihaicux".DIRECTORY_SEPARATOR."cuxframework".DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."CuxFramework".DIRECTORY_SEPARATOR;
        
        if (strpos($viewName, "//") !== false) { // check for "views/$viewName.$viewExtension"
            $absoluteViewPath = $this->getAbsoluteViewPath(substr($viewName, 2));
            $possiblePaths[] = $absoluteViewPath;
            $possiblePaths[] = $frameworkPath.$absoluteViewPath;
        } else {
            $moduleRelativePath = $this->getModuleRelativeViewPath($viewName); // check for "modules/$module/views/$controller/$viewName.$viewExtension"
            $appRelativePath = $this->getAppRelativePath($viewName); // check for "views/$module/$controller/$viewName.$viewExtension"
            $possiblePaths[] = $moduleRelativePath;
            $possiblePaths[] = $appRelativePath;
            $possiblePaths[] = $frameworkPath.$moduleRelativePath;
            $possiblePaths[] = $frameworkPath.$appRelativePath;
        }
        
        $realPath = "";
        foreach ($possiblePaths as $path){
            if (file_exists($path) && is_readable($path)){
                $realPath = $path;
                break;
            }
        }
        
        ob_start();
        
        if ($realPath){
            if (!empty($params)) {
                extract($params);
            }
            if (!@include($path)) {
                throw new \Exception(Cux::translate("error", "View not found: {view}", array(
                    "{view}" => $viewName
                )), 501);
            }
        } else {
            throw new \Exception(Cux::translate("error", "View not found: {view}", array(
                "{view}" => $viewName
            )), 501);
        }
       
        return ob_get_clean();
    }

}
