<?php

namespace CuxFramework\components\session;

use CuxFramework\utils\CuxBaseObject;
use CuxFramework\utils\Cux;
use CuxFramework\components\log\CuxLogger;

class CuxCachedSession extends CuxSession {

    public function config(array $config) {
        parent::config($config);
        
        $this->setupSession();
    }

    public function destroy($sessId) {
        $this->log('destroy(' . $sessId . ')');
        $_SESSION = null;
        return Cux::getInstance()->cache->delete($sessId);
    }

    public function gc($maxLifeTime) {
        // this is not required because  caching systems auto-expire the data
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
        return Cux::getInstance()->cache->get($this->id()) ? : "";
    }

    public function write($sessionId, $data) {
        $this->log('write(' . $sessionId . ', ' . $data . ')');
        return Cux::getInstance()->cache->set($this->id(), $data, time() + $this->lifeTime);
    }

}
