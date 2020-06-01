<?php

namespace CuxFramework\components\session;

use CuxFramework\utils\CuxBaseObject;
use CuxFramework\utils\Cux;
use CuxFramework\components\log\CuxLogger;

class CuxMemcachedSession extends CuxSession {

    public $servers = array();
    private $_memcached;

    public function config(array $config) {
        parent::config($config);   
        
        $this->_memcached = new \Memcached();
        if (is_array($this->servers) && !empty($this->servers)) {
            foreach ($this->servers as $server) {
                $this->_memcached->addServer($server["host"], $server["port"]);
            }
        }
        
        $this->setupSession();
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

}
