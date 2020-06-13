<?php

/**
 * CuxCachedSession class file
 * 
 * @package Components
 * @subpackage Session
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\components\session;

use CuxFramework\utils\CuxBaseObject;
use CuxFramework\utils\Cux;
use CuxFramework\components\log\CuxLogger;

/**
 * Simple class that stores SESSION details using the framework Caching system
 */
class CuxCachedSession extends CuxSession {

    /**
     * Setup object instance properties
     * @param array $config
     */
    public function config(array $config) {
        parent::config($config);
        
        $this->setupSession();
    }

    /**
     * Close SESSION & clear SESSION data
     * @param string $sessionId
     * @return boolean
     */
    public function destroy($sessionId) {
        $this->log('destroy(' . $sessionId . ')');
        $_SESSION = null;
        return Cux::getInstance()->cache->delete($sessionId);
    }

    /**
     * Garbage collection - delete SESSION data older than a given lifeTime
     * @param int $maxLifeTime
     * @return boolean
     */
    public function gc($maxLifeTime) {
        // this is not required because  caching systems auto-expire the data
        $this->log('gc(' . $maxLifeTime . ')');
        return $this->end();
    }

    /**
     * Start SESSION
     * @param string $savePath
     * @param string $sessionName
     * @return boolean
     */
    public function open($savePath, $sessionName) {
        $this->log('open(' . $savePath . ', ' . $sessionName . ')');        
        return true;
    }

    /**
     * Close SESSION
     * @return boolean
     */
    public function close() {
        $this->log('close');
        return true;
    }

    /**
     * Read SESSION data
     * @param string $sessionId
     * @return mixed
     */
    public function read($sessionId) {
        $this->log('read(' . $sessionId . ')');
        return Cux::getInstance()->cache->get($this->id()) ? : "";
    }

    /**
     * Write SESSION data
     * @param string $sessionId
     * @param mixed $data
     * @return bool
     */
    public function write($sessionId, $data) {
        $this->log('write(' . $sessionId . ', ' . $data . ')');
        return Cux::getInstance()->cache->set($this->id(), $data, time() + $this->lifeTime);
    }

}
