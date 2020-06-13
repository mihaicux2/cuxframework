<?php

/**
 * CuxMemcachedSession class file
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
 * Simple class that stores SESSION data using the Memcached class
 */
class CuxMemcachedSession extends CuxSession {

    /**
     * The list of Memcache servers, defined with "host" and "port" properties
     * @var array
     */
    public $servers = array();
    
    /**
     * Instance of the Memcached object
     * @var Memcached 
     */
    private $_memcached;

    /**
     * Setup object instance properties
     * @param array $config
     */
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

    /**
     * Close SESSION & clear SESSION data
     * @param string $sessionId
     * @return boolean
     */
    public function destroy($sessionId) {
        $this->log('destroy(' . $sessionId . ')');
        $_SESSION = null;
        return $this->_memcached->delete($sessionId);
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
        // this is not required because Memcached auto-expires the data
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
        return $this->_memcached->get($this->id()) ? : "";
    }

    /**
     * Write SESSION data
     * @param string $sessionId
     * @param mixed $data
     * @return bool
     */
    public function write($sessionId, $data) {
        $this->log('write(' . $sessionId . ', ' . $data . ')');
        return $this->_memcached->set($this->id(), $data, time() + $this->lifeTime);
    }

}
