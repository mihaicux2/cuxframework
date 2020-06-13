<?php

/**
 * Class for the default PHP Session handler
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
 * Default PHP session_handler.
 * @link https://www.php.net/manual/en/class.sessionhandlerinterface.php This class is taken from PHP.net
 */
class CuxFileSession extends CuxSession {

    /**
     * The location of the SESSION files
     * @var string
     */
    public $savePath = "/tmp/sessions"; 

    /**
     * Setup object instance properties
     * @param array $config
     */
    public function config(array $config) {
        parent::config($config);

        session_save_path($this->savePath);
        
        $this->setupSession();
    }

    /**
     * Start SESSION
     * @param string $savePath
     * @param string $sessionName
     * @return boolean
     */
    public function open($savePath, $sessionName) {
        if ($this->debug){
            $this->log('open(' . $savePath . ', ' . $sessionName . ')');
        }
        $this->savePath = $savePath;
        if (!is_dir($this->savePath)) {
            mkdir($this->savePath, 0755);
        }

        return true;
    }

    /**
     * Close SESSION
     * @return boolean
     */
    public function close() {
        if ($this->debug){
            $this->log('close');
        }
        return true;
    }

    /**
     * Read SESSION data
     * @param string $sessionId
     * @return type
     */
    public function read($sessionId) {
        if ($this->debug){
            $this->log('read(' . $sessionId . ')');
        }
        return (string) @file_get_contents("{$this->savePath}/sess_{$sessionId}");
    }

    /**
     * Write SESSION data
     * @param string $sessionId
     * @param mixed $data
     * @return bool
     */
    public function write($sessionId, $data) {
        if ($this->debug){
            $this->log('write(' . $sessionId . ', ' . $data . ')');
        }
        return file_put_contents("{$this->savePath}/sess_{$sessionId}", $data) === false ? false : true;
    }

    /**
     * Close SESSION & clear SESSION data
     * @param string $sessionId
     * @return boolean
     */
    public function destroy($sessionId) {
        if ($this->debug){
           $this->log('destroy(' . $sessId . ')');
        }
        $file = "{$this->savePath}/sess_{$sessionId}";
        if (file_exists($file)) {
            unlink($file);
        }
        
        $_SESSION = null;
        
        return true;
    }

    /**
     * Garbage collection - delete SESSION data older than a given lifeTime
     * @param int $maxLifeTime
     * @return boolean
     */
    public function gc($maxLifeTime) {
        if ($this->debug){
            $this->log('gc(' . $maxLifeTime . ')');
        }
        foreach (glob("$this->savePath/sess_*") as $file) {
            if (filemtime($file) + $maxLifeTime < time() && file_exists($file)) {
                unlink($file);
            }
        }

        return true;
    }
    
}
