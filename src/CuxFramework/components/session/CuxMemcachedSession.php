<?php

namespace components\session;

use CuxFramework\utils\CuxBaseObject;
use CuxFramework\utils\Cux;
use CuxFramework\components\log\CuxLogger;

class CuxMemcachedSession extends CuxBaseObject implements \SessionHandlerInterface, \SessionIdInterface {

    public $key = "defaultEncryptionKey"; // you should change this
    public $servers = array();
    public $keyPrefix = "";
    public $lifeTime = 1800;
    private $_memcached;
    public $restoreFromCookie = true;
    public $sessionName = "defaultSessionName";

    public function config(array $config) {
        parent::config($config);   
        
        $this->_memcached = new \Memcached();
        if (is_array($this->servers) && !empty($this->servers)) {
            foreach ($this->servers as $server) {
                $this->_memcached->addServer($server["host"], $server["port"]);
            }
        }
        
        session_set_save_handler($this, true);
        
        @session_regenerate_id(true);
        
        session_set_cookie_params(
            array(
                "lifetime" => $this->lifeTime,
                "path" => "/",
                "domain" => Cux::getInstance()->request->getServerValue("SERVER_NAME"),
                "secure" => false,
                "httponly" => $this->httpOnly,
            )
        );
        
        @session_name($this->sessionName);
        $ok = @session_start();
        if (!$ok){
            @session_regenerate_id(true);
            @session_start();
        }
        
        if ($this->restoreFromCookie){
            setcookie(session_name(), session_id(), time()+$this->lifeTime, "/", Cux::getInstance()->request->getServerValue("SERVER_NAME"), $this->secureCookie, $this->httpOnly);
        }
    }
    
    private function buildKey($key) {
        return $this->keyPrefix . $this->encrypt($key, $this->key);
    }

    public function create_sid() {
        $this->log('create_sid');
        return md5(openssl_random_pseudo_bytes(32)); // caractere citibile...
    }

    public function destroy($sessId) {
        $this->log('destroy(' . $sessId . ')');
        $_SESSION = null;
        return $this->_memcached->delete($sessId);
    }

    public function end() {
        return $this->destroy($this->id());
    }

    public function gc($maxLifeTime) {
        // this is not required because Memcached auto-expires the data
        $this->log('gc(' . $maxLifeTime . ')');
        return $this->end();
    }

    public function open($savePath, $sessionName) {
        $this->log('open(' . $savePath . ', ' . $sessionName . ')');        
        return true;
    }

    public function close() {
        $this->log('close');
        return true;
    }

    public function read($sessionId) {
        $this->log('read(' . $sessionId . ')');
        return $this->_memcached->get($this->id()) ? : "";
    }

    public function write($sessionId, $data) {
        $this->log('write(' . $sessionId . ', ' . $data . ')');
        return $this->_memcached->set($this->id(), $data, time() + $this->lifeTime);
    }

    // encrypt all the data kept in the session variables
    public function set($key, $value) {
        $_SESSION[$this->buildKey($key)] = $this->encrypt($value, $this->key);
        
    }

    public function get($key) {
        $key = $this->buildKey($key);
        return isset($_SESSION[$key]) ? $this->decrypt($_SESSION[$key], $this->key) : FALSE;
    }

    public function id() {
        return $this->keyPrefix . session_id();
    }

    private function log($action) {
        $msg = "SESSION_LOG <".$this->id()."@".$_SERVER["REQUEST_URI"].">: ".$action;
        if (Cux::getInstance()->hasComponent("logger")){
            Cux::log(CuxLogger::INFO, $msg);
        }
    }

}
