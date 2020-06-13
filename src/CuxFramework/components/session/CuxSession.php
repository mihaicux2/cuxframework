<?php

/**
 * CuxSession abstract class file
 * 
 * @package Components
 * @subpackage Session
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\components\session;

use CuxFramework\utils\Cux;
use CuxFramework\utils\CuxBaseObject;

/**
 * Abstract class to be used as a base for future work.
 * Extend this class to handle user sessions
 */
abstract class CuxSession extends CuxBaseObject implements \SessionHandlerInterface, \SessionIdInterface {

    /**
     * Flag for session debugging
     * @var bool
     */
    public $debug = false;
    
    /**
     * Flag for SESSION data key encryption
     * @var bool
     */
    public $useEncryption = true;
    
    /**
     * The default encryption key
     * @var string
     */
    public $key = "defaultEncryptionKey"; // you should change this
    
    /**
     * Prefix for SESSION data keys
     * @var string
     */
    public $keyPrefix = "";
    
    /**
     * Default SESSION lifetime
     * @var int
     */
    public $lifeTime = 1800;
    
    /**
     * Secure connection SESSION cookie
     * @var bool
     */
    public $secureCookie = true;
    
    /**
     * Set the "httponly" flag when setting the session cookie
     * @var bool
     */
    public $httpOnly = true;
    
    /**
     * Use existing (client) cookies to restore a previous SESSION
     * @var bool
     */
    public $restoreFromCookie = true;

    /**
     * Encrypt a given text ( only if the $useEncryption property is set to true )
     * @param type $edata
     * @param type $password
     * @return type
     */
    protected function decrypt($edata, $password) {
        if ($this->useEncryption) {
            return parent::decrypt($edata, $password);
        }
        return $edata;
    }

    /**
     * Decrypt a given text  ( only if the $useEncryption property is set to true )
     * @param string $data
     * @param string $password
     * @return string
     */
    protected function encrypt($data, $password) {
        if ($this->useEncryption) {
            return parent::encrypt($data, $password);
        }
        return $data;
    }

    /**
     * Build the SESSION key, using the $keyPrefix property and the encrypted  ( only if the $useEncryption property is set to true ) version of the provided $key argument
     * @param string $key
     * @return string
     */
    protected function buildKey($key) {
        return $this->keyPrefix . $this->encrypt($key, $this->key);
    }
    
    /**
     * SESSION initialization
     */
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
    
    /**
     * Log/debug SESSION info
     * @param string $action
     */
    protected function log($action) {
        if ($this->debug){
            $msg = "SESSION_LOG <" . $this->id() . "@" . $_SERVER["REQUEST_URI"] . ">: " . $action;
            if (Cux::getInstance()->hasComponent("logger")) {
                Cux::log(CuxLogger::INFO, $msg);
            }
        }
    }

    /**
     * Create a new SESSION id
     * @return string
     */
    public function create_sid(): string {
        if ($this->debug){
            $this->log('create_sid');
        }
        return md5(openssl_random_pseudo_bytes(32)); // caractere citibile...
    }

    /**
     * Store a new value in the SESSION
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value) {
        $_SESSION[$this->buildKey($key)] = $this->encrypt($value, $this->key);
    }

    /**
     * Get a value from the SESSION
     * @param string $key
     * @return mixed
     */
    public function get($key) {
        $key = $this->buildKey($key);
        return isset($_SESSION[$key]) ? $this->decrypt($_SESSION[$key], $this->key) : FALSE;
    }

    /**
     * Get the current SESSION id
     * @return string
     */
    public function id() {
        return $this->keyPrefix . session_id();
    }
    
    /**
     * Close the current SESSION
     * @return bool
     */
    public function end() {
        return $this->destroy($this->id());
    }
    
}
