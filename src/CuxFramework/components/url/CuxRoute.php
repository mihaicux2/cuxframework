<?php

/**
 * CuxRoute class file
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
 * URL Route class
 * The urlManages uses a predefined list of URL Routes to identify and map the current request
 */
class CuxRoute {
    
    /**
     * The Route pattern to be matched against the current URL request
     * @var string
     */
    private $_routePattern;
    
    /**
     * The Route real path, if request matches the pattern
     * @var string 
     */
    private $_rewritePattern;
    
    /**
     * The current URL request path
     * @var string
     */
    private $_requestPath;
    
    /**
     * The Route pattern, after pre-processing (replace app patterns to php patterns - i.e. <d+> => (/[0-9]+)/</code>
     * @var string
     */
    private $_processedPattern;
    
    /**
     * The URL request path after pre-processing
     * @var string
     */
    private $_processedPath;
    
    /**
     * The URL request full path
     * @var string
     */
    private $_processedRequest;
    
    /**
     * Formatted URL request
     * @var string
     */
    private $_prettyRequest;
    
    /**
     * The list of URL request parameters, after the route matching process
     * @var array
     */
    private $_params;
    
    /**
     * If the Route matches the current URL request, set the matched pattern
     * @var string 
     */
    private $_matchedPattern = null;
    
    /**
     * If the Route matches the current URL request, set the matched path
     * @var string 
     */
    private $_matchedPath = null;
    
    /**
     * If the Route matches the current URL request, set the matched route
     * @var string 
     */
    private $_routePath = null;
    
    /**
     * Class constructor
     * @param string $routePattern The pattern to be matched
     * @param string $rewritePattern If match, this is the real route
     * @param CuxRequest $request The current URL request
     * @param string $requestPath If defined, override the $request->getPath() with this value
     */
    public function __construct(string $routePattern, string $rewritePattern, CuxRequest $request, string $requestPath = null) {
        
        $this->setRoutePattern($routePattern);
        $this->setRewritePattern($rewritePattern);
        $this->_requestPath = is_null($requestPath) ? $request->getPath() : $requestPath;;
        
        $this->_processedPattern = $this->processPattern($this->_routePattern);
        
        $this->matchesUrl();
        
//        if ($this->isRouteMatched()){
//            print_r($this->getDetails());
//        }
        
    }
    
    /**
     * Get the formatted details about the current URL Request, as processed by the current Route
     * @return array
     */
    public function getDetails(){
        return array(
            "routePatterm" => $this->_routePattern,
            "rewritePattern" => $this->_rewritePattern,
            "processedPattern" => $this->_processedPattern,
            "requestPath" => $this->_requestPath,
            "result" => $this->_matchedPattern,
            "processedRequest" => $this->_processedRequest,
            "prettyRequest" => $this->_prettyRequest,
            "path" => $this->_matchedPath,
            "routePath" => $this->_routePath,
            "params" => $this->_params
        );
    }
    
    /**
     * Check if the current Route matched the URL request
     * @return bool
     */
    public function isRouteMatched(): bool{
        return !is_null($this->_matchedPattern);
    }
    
    /**
     * Setter for the $_routePattern property
     * @param string $routePattern
     */
    public function setRoutePattern(string $routePattern){
        $this->_routePattern = $routePattern;
    }
    
    /**
     * Getter for the $_routePattern property
     * @return string
     */
    public function getRoutePattern(): string{
        return $this->_routePattern;
    }
    
    /**
     * Setter for the $_rewritePattern property
     * @param string $rewritePattern
     */
    public function setRewritePattern(string $rewritePattern){
        $this->_rewritePattern = $rewritePattern;
    }
    
    /**
     * Getter for the $__rewritePattern property
     * @return string
     */
    public function getRewritePattern(): string{
        return $this->_rewritePattern;
    }
    
    /**
     * Process the app Route pattern to PHP RegExp pattern
     * @param string $route
     * @return string
     */
    private function processPattern(string $route): string {
        $route = str_replace(array("[", "]"), array("\\[", "\\]"), $route);
        $route = preg_replace("/<d(\+*)>/i", "([0-9]\$1)", $route);
        $route = preg_replace("/<w(\+*)>/i", "([a-zA-Z0-9-_\.]\${1})", $route);
        return "|" . str_replace("/", "\\/", trim($route)) . "|im";
    }
    
    /**
     * Check if the current Route matches the URL request
     * @return bool
     */
    public function matchesUrl(): bool {
        $ret = preg_match($this->_processedPattern, $this->_requestPath, $matches);
        if ($ret){
            $match = $this->_rewritePattern;
            if (($x = count($matches)) > 1) {
                for ($i = 1; $i < $x; $i++) {
                    $match = str_replace("\$$i", $matches[$i], $match);
                }
            }

            $this->_matchedPattern = $match;
            $this->_params = $this->getParams($match);
            $this->_matchedPath = $this->getPath($match);
            
            $this->_processedRequest = $this->_matchedPath;
            $this->_prettyRequest = $this->_matchedPath;
            
            $this->_routePath = $this->_requestPath;
            
            if (!empty($this->_params)){
                $this->_processedRequest .= "?".http_build_query($this->_params);
                
                foreach($this->_params as $key => $val){
                    if (!is_array($val)){
                        $this->_prettyRequest .= "/{$key}/$val";
                        $this->_routePath = str_replace("/{$key}/$val", "", $this->_routePath);
                    } else {
                        foreach ($val as $val2){
                            $this->_prettyRequest .= "/{$key}[]/$val2";
                            
                            $this->_routePath = str_replace("/{$key}[]/$val2", "", $this->_routePath);
                        }
                    }
                }
            }
            
        }        
        return $ret;
    }
    
    /**
     * Get the path form a request ( without the extra query params )
     * @param string $path
     * @return string
     */
    public function getPath(string $path): string{
         if (($pos = strpos($path, "?")) !== false) {
             return substr($path, 0, $pos);
         }
         return $path;
    }
    
    /**
     * Get the request params
     * @param string $path
     * @return array
     */
    public function getParams(string $path): array {
        $ret = array();
        if (($pos = strpos($path, "?")) !== false) {
            parse_str(substr($path, $pos + 1), $ret);
        }
        $ret = array_merge($ret, $_GET);
        
        $otherParams = preg_replace($this->_processedPattern, "", $this->_requestPath);
        while (substr($otherParams, 0, 1) == "/"){
            $otherParams = substr($otherParams, 1);
        }
        while (substr($otherParams, -1) == "/"){
            $otherParams = substr($otherParams, 0, -1);
        }
//        echo "Other params: ".$otherParams.":".strpos($otherParams, "/").PHP_EOL;
        if (($pos = strpos($otherParams, "/")) !== false) {
            $params = explode("/", $otherParams);            
            $key = $item = "";
            $isArray = false;
            $keyKey = "";
            foreach ($params as $it => $item){
                if ($it % 2 == 0){
                    $key = $item;
                    if (($pos = strpos($key, "[")) !== false){
                        $keyKey = substr($key, $pos+1, -1);
                        $key = substr($key, 0, $pos);
                        $isArray = true;
                    } else {
                        $isArray = false;
                        $keyKey = "";
                    }
                } else {
                    $value = $item;
                    if ($isArray){
                        if ($keyKey){
                            $ret[$key][$keyKey] = $value;
                        } else {
                            $ret[$key][] = $value;
                        }
                    } else {
                        $ret[$key] = $value;
                    }
                }
            }
        }

        return $ret;
    }
    
}