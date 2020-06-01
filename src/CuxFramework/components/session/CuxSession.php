<?php

namespace CuxFramework\components\session;

use CuxFramework\utils\Cux;
use CuxFramework\utils\CuxBaseObject;

abstract class CuxSession extends CuxBaseObject implements \SessionHandlerInterface, \SessionIdInterface {

    public $debug = false;
    public $useEncryption = true;
    public $key = "defaultEncryptionKey"; // you should change this
    public $keyPrefix = "";
    public $lifeTime = 1800;
    public $secureCookie = true;
    public $httpOnly = true;
    public $restoreFromCookie = true;

    protected function decrypt($edata, $password) {
        if ($this->useEncryption) {
            return parent::decrypt($edata, $password);
        }
        return $edata;
    }

    protected function encrypt($data, $password) {
        if ($this->useEncryption) {
            return parent::encrypt($data, $password);
        }
        return $data;
    }

    protected function buildKey($key) {
        return $this->keyPrefix . $this->encrypt($key, $this->key);
    }
    
    protected function setupSession(){
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
    
    protected function log($action) {
        if ($this->debug){
            $msg = "SESSION_LOG <" . $this->id() . "@" . $_SERVER["REQUEST_URI"] . ">: " . $action;
            if (Cux::getInstance()->hasComponent("logger")) {
                Cux::log(CuxLogger::INFO, $msg);
            }
        }
    }

    public function create_sid(): string {
        if ($this->debug){
            $this->log('create_sid');
        }
        return md5(openssl_random_pseudo_bytes(32)); // caractere citibile...
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
    
    public function end() {
        return $this->destroy($this->id());
    }
    
}
