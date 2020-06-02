<?php

/**
 * Class for the default PHP Session handler
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

    public $savePath = "/tmp/sessions"; 

    public function config(array $config) {
        parent::config($config);

        session_save_path($this->savePath);
        
        $this->setupSession();
    }

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

    public function close() {
        if ($this->debug){
            $this->log('close');
        }
        return true;
    }

    public function read($sessionId) {
        if ($this->debug){
            $this->log('read(' . $sessionId . ')');
        }
        return (string) @file_get_contents("{$this->savePath}/sess_{$sessionId}");
    }

    public function write($sessionId, $data) {
        if ($this->debug){
            $this->log('write(' . $sessionId . ', ' . $data . ')');
        }
        return file_put_contents("{$this->savePath}/sess_{$sessionId}", $data) === false ? false : true;
    }

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
