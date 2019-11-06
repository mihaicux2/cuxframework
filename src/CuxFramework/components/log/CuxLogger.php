<?php

namespace CuxFramework\components\log;

use CuxFramework\utils\CuxSingleton;

abstract class CuxLogger extends CuxSingleton{
    
    const DEBUG     = 1;
    const INFO      = 2;
    const NOTICE    = 4;
    const WARNING   = 8;
    const ERROR     = 16;
    const CRITICAL  = 32;
    const ALERT     = 64;
    const EMERGENCY = 128;
    
    public $logLevel = 255;
    
//    const DEBUG     = 255; // 1 + 2 + 4 + 8 + 16 + 32 + 64 + 128
//    const INFO      = 127; // 1 + 2 + 4 + 8 + 16 + 32 + 64
//    const NOTICE    = 63;  // 1 + 2 + 4 + 8 + 16 + 32
//    const WARNING   = 31;  // 1 + 2 + 4 + 8 + 16
//    const ERROR     = 15;  // 1 + 2 + 4 + 8
//    const CRITICAL  = 7;   // 1 + 2 + 3
//    const ALERT     = 3;   // 1 + 2
//    const EMERGENCY = 1;   // 1
    
    public function emergency($message, array $context = array()) {
        $this->log(self::EMERGENCY, $message, $context);
    }
    
    public function alert($message, array $context = array()) {
        $this->log(self::ALERT, $message, $context);
    }
    
    public function critical($message, array $context = array()) {
        $this->log(self::CRITICAL, $message, $context);
    }
    
    public function error($message, array $context = array()) {
        $this->log(self::ERROR, $message, $context);
    }
    
    public function warning($message, array $context = array()) {
        $this->log(self::WARNING, $message, $context);
    }
    
    public function notice($message, array $context = array()) {
        $this->log(self::NOTICE, $message, $context);
    }
    
    public function info($message, array $context = array()) {
        $this->log(self::INFO, $message, $context);
    }
    
    public function debug($message, array $context = array()) {
        $this->log(self::DEBUG, $message, $context);
    }
    
    abstract public function log(int $level, string $message, array $context = array()): bool;
    
}

