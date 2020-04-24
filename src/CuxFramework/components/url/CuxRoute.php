<?php

namespace CuxFramework\components\url;

use CuxFramework\utils\CuxBaseObject;
use CuxFramework\components\request\CuxRequest;
use CuxFramework\utils\Cux;

class CuxRoute {
    
    private $_routePattern;
    private $_rewritePattern;
    private $_requestPath;
    private $_processedPattern;
    private $_processedPath;
    private $_processedRequest;
    private $_prettyRequest;
    
    private $_params;
    
    private $_matchedPattern = null;
    private $_matchedPath = null;
    private $_routePath = null;
    
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
    
    public function isRouteMatched(): bool{
        return !is_null($this->_matchedPattern);
    }
    
    public function setRoutePattern(string $routePattern){
        $this->_routePattern = $routePattern;
    }
    
    public function getRoutePattern(): string{
        return $this->_routePattern;
    }
    
    public function setRewritePattern(string $rewritePattern){
        $this->_rewritePattern = $rewritePattern;
    }
    
    public function getRewritePattern(): string{
        return $this->_rewritePattern;
    }
    
    private function processPattern(string $route): string {
        $route = preg_replace("/<d(\+*)>/i", "([0-9]\$1)", $route);
        $route = preg_replace("/<w(\+*)>/i", "([a-zA-Z0-9-_\.]\${1})", $route);
//        return "|" . str_replace("/", "\\/", trim($route)) . "$|im";
        return "|" . str_replace("/", "\\/", trim($route)) . "|im";
    }
    
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
    
    public function getPath(string $path): string{
         if (($pos = strpos($path, "?")) !== false) {
             return substr($path, 0, $pos);
         }
         return $path;
    }
    
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