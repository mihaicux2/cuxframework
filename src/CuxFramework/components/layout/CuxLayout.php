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

    public function renderPartial(string $name, array $params = array()): string {
        if (!empty($params)) {
            extract($params);
        }
        ob_start();
        $path = $this->viewsFolder . DIRECTORY_SEPARATOR;
        if (strpos($name, "//") !== false) {
            $name = substr($name, 2);
        } else {
            if ($this->_moduleName) {
                $path .= $this->_moduleName . DIRECTORY_SEPARATOR;
                if ($this->_controllerName) {
                    $path .= $this->_controllerName . DIRECTORY_SEPARATOR;
                }
            }
        }
        $path .= $name . $this->viewExtension;
        
        if (file_exists($path) && is_readable($path)){
            if (!@include($path)) {
                throw new \Exception(Cux::translate("error", "View not found: {view}", array(
                    "{view}" => $name
                )), 501);
            }
        } else {
            $path = "vendor/mihaicux/cuxframework/src/CuxFramework/".$path;
            if (!@include($path)) {
                throw new \Exception(Cux::translate("error", "View not found: {view}", array(
                    "{view}" => $name
                )), 501);
            }
        }
        return ob_get_clean();
    }

}
