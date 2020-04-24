<?php

namespace CuxFramework\components\url;

use CuxFramework\utils\CuxBaseObject;
use CuxFramework\components\request\CuxRequest;
use CuxFramework\utils\Cux;

class CuxUrlManager extends CuxBaseObject {

    public $defaultAction = "cuxDefault/cuxDefault/index";
    public $routes = array();
    
    public $_routes = array();
    public $_crtRoute;

    public function config(array $config) {
        parent::config($config);
        
        $request = Cux::getInstance()->request;
        $crtPath = $request->getPath();
        if (!empty($crtPath) && $crtPath != "/"){
            $defaultRoute = new CuxRoute($this->defaultAction, $this->defaultAction, $request);
        } else {
            $defaultRoute = new CuxRoute($this->defaultAction, $this->defaultAction, $request, $this->defaultAction);
        }
        
        if (isset($config["routes"])){
            foreach ($config["routes"] as $route => $match){
                $this->addRoute($route, $match, $request);
            }
        }
        
        if (!$this->_crtRoute){
            $this->_crtRoute = $defaultRoute;
            $this->_routes[] = $defaultRoute;
        }
    }

    public function addRoute(string $route, string $match, CuxRequest $request){
        $route = new CuxRoute($route, $match, $request);
        if ($route->isRouteMatched()){
            $this->_crtRoute = $route;
        }
        $this->_routes[] = $route;
    }
    
    public function getMatchedRoute(){
        return $this->_crtRoute;
    }
    
    public function getRequestParams(string $route, string $path): array {
        preg_match("|" . str_replace("/", "\\/", $route) . "|im", $path, $matches);
        return $matches;
    }
    
    public function getRoute(): string{
        if (!is_null($this->_crtRoute) && $this->_crtRoute instanceof CuxRoute){
            $details = $this->_crtRoute->getDetails();
            return $details["routePath"];
        }
        return "";
    }

    public function getBasePath(): string {
        return realpath(__DIR__ . "/../../") . "/";
    }

    public function createAbsoluteUrl($url) {
        if (($pos = strpos($url, "/") === false) || $pos > 0) {
            $url = "/" . $url;
        }

        return Cux::getInstance()->request->getBaseURL() . $url;
    }

}
