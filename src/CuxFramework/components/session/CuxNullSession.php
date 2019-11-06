<?php

namespace components\session;

use CuxFramework\utils\CuxSingleton;
use CuxFramework\utils\CuxBase;

class CuxNullSession extends CuxSingleton implements \SessionHandlerInterface, \SessionIdInterface {

    private $_sId;

    public static function config(array $config): void {
        $ref = static::getInstance();
        CuxBase::config($ref, $config);
    }

    private function buildKey($key) {
        return $this->keyPrefix . $this->encrypt($key, $this->key);
    }

    public function create_sid() {
        $this->_sId = openssl_random_pseudo_bytes(32);
        return $this->_sId;
    }

    public function destroy($sessId) {
        return true;
    }

    public function end() {
        return $this->destroy($this->id());
    }

    public function gc($maxLifeTime) {
        return true;
    }

    public function open($savePath, $sessionName) {
        return true;
    }

    public function close() {
        return true;
    }

    public function read($sessionId) {
        return "";
    }

    public function write($sessionId, $data) {
        return true;
    }

    // encrypt all the data kept in the session variables
    public function set($key, $value) {}

    public function get($key) {
        return false;
    }

    public function id() {
        return $this->keyPrefix . $this->_sId;
    }

    private function log($action) { }

}
