<?php

/**
 * CuxUrlManager class file
 * 
 * @package Components
 * @subpackage URL
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\components\url;

use CuxFramework\utils\CuxBaseObject;
use CuxFramework\components\request\CuxRequest;
use CuxFramework\utils\Cux;

/**
 * URL Request manager.
 * Using a list of Routes, the urlManager component determines the MVC route for a given URL Request
 */
class CuxUrlManager extends CuxBaseObject {

    /**
     * The default MVC Route
     * @var string
     */
    public $defaultAction = "cux/cux/index";
    
    /**
     * Key-valued array with the list of pattern and matched routes
     * @var array
     */
    public $routes = array();
    
    /**
     * The list of Route objects that will be matched against the current URL Request
     * @var array 
     */
    public $_routes = array();
    
    /**
     * If found, the first Route that matched the current URL Request
     * @var mixed
     */
    public $_crtRoute;

    /**
     * Setup class properties
     * @param array $config
     */
    public function config(array $config) {
        parent::config($config);
        
        $request = Cux::getInstance()->request;
        $crtPath = $request->getPath();
        
//        if (!empty($crtPath) && $crtPath != "/"){
////            $defaultRoute = new CuxRoute($this->defaultAction, $this->defaultAction, $request);
//            $defaultRoute = new CuxRoute($crtPath, $crtPath, $request, $crtPath);
//        } else {
//            $defaultRoute = new CuxRoute($this->defaultAction, $this->defaultAction, $request, $this->defaultAction);
//        }
        
        if (isset($config["routes"])){
            foreach ($config["routes"] as $route => $match){
                $this->addRoute($route, $match, $request);
            }
        }
        
        if (!$this->_crtRoute){
            if (!empty($crtPath) && $crtPath != "/"){
                if (substr($crtPath, 0, 1) == "/"){
                    $crtPath = substr($crtPath, 1);
                }
                $pathParts = explode("/", $crtPath);
                if (count($pathParts) >= 2){
                    $routePath = "/".implode("/", array_slice($pathParts, 0, 3)); // build the module/controller/action path :)
                }
                else{
                    $routePath = $crtPath;
                }
//                $this->addRoute($this->defaultAction, $this->defaultAction, $request, $crtPath);
                $this->addRoute($routePath, $routePath, $request);
            } else {
                $this->addRoute($this->defaultAction, $this->defaultAction, $request, $this->defaultAction);
            }
        }
        
//        if (!$this->_crtRoute){
//            $this->_crtRoute = $defaultRoute;
//            $this->_routes[] = $defaultRoute;
//        }
    }

    /**
     * Add a new Route to the $routes list
     * @param string $route app route pattern
     * @param string $match MVC route
     * @param CuxRequest $request The current URL Request
     * @param string $requestPath Override the URL Request's request path
     */
    public function addRoute(string $route, string $match, CuxRequest $request, string $requestPath = null){
        $route = new CuxRoute($route, $match, $request, $requestPath);
        if (!$this->_crtRoute && $route->isRouteMatched()){ // first matched is the current route :)
            $this->_crtRoute = $route;
        }
        $this->_routes[] = $route;
    }
    
    /**
     * Getter for the $_crtRoute property
     * @return mixed
     */
    public function getMatchedRoute(){
        return $this->_crtRoute;
    }
    
    /**
     * Get request parameters for a given MVR route
     * @param string $route The current MVC request
     * @param string $path The current URL request path
     * @return array
     */
    public function getRequestParams(string $route, string $path): array {
        preg_match("|" . str_replace("/", "\\/", $route) . "|im", $path, $matches);
        return $matches;
    }
    
    /**
     * Get the MVC path for the current URL request
     * @return string
     */
    public function getRoute(): string{
        if (!is_null($this->_crtRoute) && $this->_crtRoute instanceof CuxRoute){
            $details = $this->_crtRoute->getDetails();
            return $details["routePath"];
        }
        return "";
    }

    /**
     * RealPath for the app root folder
     * @return string
     */
    public function getBasePath(): string {
        return realpath(__DIR__ . "/../../") . "/";
    }

    /**
     * Create an absolute (domain-specific) URL link
     * @param string $url
     * @return type
     */
    public function createAbsoluteUrl($url) {
        if (($pos = strpos($url, "/") === false) || $pos > 0) {
            $url = "/" . $url;
        }

        return Cux::getInstance()->request->getBaseURL() . $url;
    }

}
