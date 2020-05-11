<?php

namespace CuxFramework\components\request;
use CuxFramework\utils\Cux;

/*
 * http://php.net/manual/en/geoip.setup.php:
 * 
 * wget http://geolite.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz
 * gunzip GeoLiteCity.dat.gz
 * sudo mkdir -v /usr/share/GeoIP
 * sudo mv -v GeoLiteCity.dat /usr/share/GeoIP/GeoIPCity.dat
 * sudo apt-get install php-geoip
 * 
 */

use CuxFramework\utils\CuxBaseObject;

class CuxRequest extends CuxBaseObject {

    private $_path;
    private $_params;
    private $_scriptName;
    
    public function config(array $config) {
        parent::config($config);
        
        $this->preProcessRequest();
    }

    private function preProcessRequest(){
        $uri = $this->getUri();
        $queryInRequest = strpos($uri, "?");
        if ($queryInRequest !== false){
            $this->_path = substr($uri, 0, $queryInRequest);
            parse_str(substr($uri, $queryInRequest+1), $this->_params);
            
        } else {
            $this->_path = $uri;
            parse_str($this->getServerValue("QUERY_STRING"), $this->_params);
        }
        
        $this->_scriptName = basename($this->getServerValue("SCRIPT_NAME"));
    }
    
    /**
     * Returns the IP of the current visiting user
     * 
     * @return string
     */
    public function getIp(): string {

        return long2ip(rand(0, "4294967295"));
        
        // test the presence of HTTP_X_FORWARDED_FOR
        $fwd = $this->getServerValue("HTTP_X_FORWARDED_FOR");
        if ($fwd) {
            // we have a proxy connection
            // extract the first IP
            $forwardedFor = $fwd;
            if (strpos($forwardedFor, ',') !== false) {
                $forwardedFor = substr($forwardedFor, 0, strpos($forwardedFor, ','));
            }
            return $forwardedFor;
        } else
            return $this->getServerValue("REMOTE_ADDR");
    }

    /**
     * Returns the detailed City information found in the GeoIP Database
     * 
     * @param hostname The hostname or IP address whose record is to be looked-up.
     * @return array Informations about the city, country, etc. of the current visitor
     */
    public function getVisitorInfo(string $hostname = "") {
        if (!$hostname) {
            $hostname = $this->getIp();
        }
        $data = null;
        if (function_exists('geoip_record_by_name') && $hostname) {
            $data = @geoip_record_by_name($hostname);
        }
        return $data;
    }

    /**
     * Returns the current visitor's browser informations
     * 
     * @return string
     */
    public function getUserAgent(): string {
        return $this->getServerValue("HTTP_USER_AGENT");
    }
    
    /**
     * Returns the current visitor's referer
     * 
     * @return string
     */
    public function getReferer(): string {
        return $this->getServerValue("HTTP_REFERER");
    }
    
    /**
     * Returns the current visitor's referer
     * 
     * @return string
     */
    public function getUri(): string {
        return $this->getServerValue("REQUEST_URI");
    }

    /**
     * Returns the SERVER value for a given key or the defaultValue if such key does not exist
     * 
     * @param string $key The searched _SERVER key
     * @param mixed $defaultValue If the _SERVER key does not exist, the method will return this param
     * @return mixed The value for the _SERVER[$key] global variable or the $defaultValue param
     */
    public function getServerValue(string $key, $defaultValue = ""){
        return isset($_SERVER[$key]) ? $_SERVER[$key] : $defaultValue;
    }
    
    /**
     * Returns the method of the current request
     * (ie. GET, POST, OPTIONS, HEAD, DELETE, PUT, PATCH)
     * 
     * @return string The current request method
     */
    public function getMethod(): string {
        $ovr = $this->getServerValue("HTTP_X_HTTP_METHOD_OVERRIDE");
        if ($ovr) {
            return strtoupper($ovr);
        } else {
            return strtoupper($this->getServerValue("REQUEST_METHOD", "GET"));
        }
    }

    /**
     * Returns whether the method of the current request is GET
     * 
     * @return boolean True if the method of current request is GET
     */
    public function isGet(): bool {
        return $this->getMethod() == 'GET';
    }

    /**
     * Returns whether the method of the current request is POST
     * 
     * @return boolean True if the method of current request is POST
     */
    public function isPost(): bool {
        return $this->getMethod() === 'POST';
    }

    /**
     * Returns whether the method of the current request is OPTIONS
     * 
     * @return boolean True if the method of current request is OPTIONS
     */
    public function isOptions(): bool {
        return $this->getMethod() === 'OPTIONS';
    }

    /**
     * Returns whether the method of the current request is HEAD
     * 
     * @return boolean True if the method of current request is HEAD
     */
    public function isHead(): bool {
        return $this->getMethod() === 'HEAD';
    }

    /**
     * Returns whether the method of the current request is DELETE
     * 
     * @return boolean True if the method of current request is DELETE
     */
    public function isDelete(): bool {
        return $this->getMethod() === 'DELETE';
    }

    /**
     * Returns whether the method of the current request is PUT
     * 
     * @return boolean True if the method of current request is PUT
     */
    public function isPut(): bool {
        return $this->getMethod() === 'PUT';
    }

    /**
     * Returns whether the method of the current request is PATCH
     * 
     * @return boolean True if the method of current request is PATCH
     */
    public function isPatch(): bool {
        return $this->getMethod() === 'PATCH';
    }

    /**
     * Returns whether the method of the current request is made with AJAX
     * 
     * @return boolean True if the method of current request is AJAX (XMLHttpRequest)
     */
    public function isAjax(): bool {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
    }

    /**
     * Returns the GET param with the given name
     * 
     * @param string $name The key for the GET param
     * @param mixed $defaultValue If the GET param does not exist, the method will return this parameter instead
     * @return mixed The requested GET value or the defaultValue parameter
     */
    public function getParam(string $name, $defaultValue = "") {
//        return isset($_GET[$name]) ? $_GET[$name] : $defaultValue;
        return isset($this->_params[$name]) ? $this->_params[$name] : $defaultValue;
    }

    /**
     * Returns the POST param with the given name
     * 
     * @param string $name The key for the POST param
     * @param mixed $defaultValue If the POST param does not exist, the method will return this parameter instead
     * @return mixed The requested POST value or the defaultValue parameter
     */
    public function getPost(string $name, $defaultValue = "") {
        return isset($_POST[$name]) ? $_POST[$name] : $defaultValue;
    }

    /**
     * Returns the GET data
     * 
     * @return array The global GET variable
     */
    public function getParams(): array{
//        return $_GET;
        
        try {
            $route = Cux::getInstance()->urlManager->getMatchedRoute();
            $routeInfo = $route->getDetails();
            $params = $routeInfo["params"];
            $this->_params = $routeInfo["params"];
        } catch (\Exception $e){}
        
        return $this->_params;
    }
    
    public function getPath(): string{
        
         try {
            $route = Cux::getInstance()->urlManager->getMatchedRoute();
            $routeInfo = $route->getDetails();
            $this->_path = $routeInfo["path"];
        } catch (\Exception $e){}
        
        return $this->_path;
    }
    
    public function getRoutePath(): string{
        try {
            $route = Cux::getInstance()->urlManager->getMatchedRoute();
            $routeInfo = $route->getDetails();
            return $routeInfo["routePath"];
        } catch (\Exception $e){}
        
        return $this->getPath();
    }
    
    public function getScriptName(): string{
        return $this->_scriptName;
    }
    
    /**
     * Returns the POST data
     * 
     * @return array The global POST variable
     */
    public function getPosts(): array{
        return $_POST;
    }
    
    public function getURLInfo(): array{
        return $params = array(
            "scheme" => $this->getServerValue("REQUEST_SCHEME"), // http, https
            "port" => $this->getServerValue("SERVER_PORT"), // 80, 443, etc.
            "serverName" => $this->getServerValue("SERVER_NAME")
        );
    }
    
    public function getBaseURL(): string{
        $params = $this->getURLInfo();
        return $params["scheme"]."://".$params["serverName"].(!in_array($params["port"], array(80, 443)) ? (":".$params["port"]) : "");
    }
    
}
