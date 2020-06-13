<?php

/**
 * CuxNullSession class file
 * 
 * @package Components
 * @subpackage Session
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\components\session;

use CuxFramework\utils\CuxBaseObject;

/**
 * Simple SESSION handler that does not store the date
 */
class CuxNullSession extends CuxBaseObject implements \SessionHandlerInterface, \SessionIdInterface {

    /**
     * Current SESSION id
     * @var string
     */
    private $_sId;

    /**
     * Setup object instance properties
     * @param array $config
     */
    public function config(array $config) {
        parent::config($config);
    }

    /**
     * Build the SESSION key, using the $keyPrefix property and the encrypted  ( only if the $useEncryption property is set to true ) version of the provided $key argument
     * @param string $key
     * @return string
     */
    private function buildKey($key) {
        return $this->keyPrefix . $this->encrypt($key, $this->key);
    }

    /**
     * Create a new SESSION id
     * @return string
     */
    public function create_sid() {
        $this->_sId = openssl_random_pseudo_bytes(32);
        return $this->_sId;
    }

    /**
     * Close SESSION & clear SESSION data
     * @param string $sessionId
     * @return boolean
     */
    public function destroy($sessionId) {
        return true;
    }

    /**
     * Close the current SESSION
     * @return bool
     */
    public function end() {
        return $this->destroy($this->id());
    }

    /**
     * Garbage collection - delete SESSION data older than a given lifeTime
     * @param int $maxLifeTime
     * @return boolean
     */
    public function gc($maxLifeTime) {
        return true;
    }

    /**
     * Start SESSION
     * @param string $savePath
     * @param string $sessionName
     * @return boolean
     */
    public function open($savePath, $sessionName) {
        return true;
    }

     /**
     * Close SESSION
     * @return boolean
     */
    public function close() {
        return true;
    }

    /**
     * Read SESSION data
     * @param string $sessionId
     * @return type
     */
    public function read($sessionId) {
        return "";
    }

    /**
     * Write SESSION data
     * @param string $sessionId
     * @param mixed $data
     * @return bool
     */
    public function write($sessionId, $data) {
        return true;
    }

    /**
     * Store a new value in the SESSION
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value) {}

    /**
     * Get a value from the SESSION
     * @param string $key
     * @return mixed
     */
    public function get($key) {
        return false;
    }

    /**
     * Get the current SESSION id
     * @return string
     */
    public function id() {
        return $this->keyPrefix . $this->_sId;
    }

    /**
     * Log/debug SESSION info
     * @param string $action
     */
    private function log($action) { }

}
