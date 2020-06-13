<?php

/**
 * CuxLogger abstract class file
 * 
 * @package Components
 * @subpackage Log
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\components\log;

use CuxFramework\utils\CuxBaseObject;

/**
 * Abstract class to be used as a starting point for a PSR-4 logging system
 */
abstract class CuxLogger extends CuxBaseObject{
    
    /**
     * Log message levels
     */
    const DEBUG     = 1;
    const INFO      = 2;
    const NOTICE    = 4;
    const WARNING   = 8;
    const ERROR     = 16;
    const CRITICAL  = 32;
    const ALERT     = 64;
    const EMERGENCY = 128;
    
    /**
     * The default logging level
     * @var int
     */
    public $logLevel = CuxLogger::EMERGENCY + CuxLogger::ALERT +CuxLogger::CRITICAL + CuxLogger::ERROR;
    
    /**
     * Log EMERGENCY messages
     * @param string $message The message to be logged
     * @param array $context The context for the message to be logged
     */
    public function emergency($message, array $context = array()) {
        $this->log(self::EMERGENCY, $message, $context);
    }
    
    /**
     * Log CRITICAL messages
     * @param string $message The message to be logged
     * @param array $context The context for the message to be logged
     */
    public function alert($message, array $context = array()) {
        $this->log(self::ALERT, $message, $context);
    }
    
    /**
     * Log EMERGENCY messages
     * @param string $message The message to be logged
     * @param array $context The context for the message to be logged
     */
    public function critical($message, array $context = array()) {
        $this->log(self::CRITICAL, $message, $context);
    }
    
    /**
     * Log ERROR messages
     * @param string $message The message to be logged
     * @param array $context The context for the message to be logged
     */
    public function error($message, array $context = array()) {
        $this->log(self::ERROR, $message, $context);
    }
    
    /**
     * Log WARNING messages
     * @param string $message The message to be logged
     * @param array $context The context for the message to be logged
     */
    public function warning($message, array $context = array()) {
        $this->log(self::WARNING, $message, $context);
    }
    
    /**
     * Log NOTICE messages
     * @param string $message The message to be logged
     * @param array $context The context for the message to be logged
     */
    public function notice($message, array $context = array()) {
        $this->log(self::NOTICE, $message, $context);
    }
    
    /**
     * Log INFO messages
     * @param string $message The message to be logged
     * @param array $context The context for the message to be logged
     */
    public function info($message, array $context = array()) {
        $this->log(self::INFO, $message, $context);
    }
    
    /**
     * Log DEBUG messages
     * @param string $message The message to be logged
     * @param array $context The context for the message to be logged
     */
    public function debug($message, array $context = array()) {
        $this->log(self::DEBUG, $message, $context);
    }
    
    /**
     * Log a message with a given logging level
     * @param int $level The logging level
     * @param string $message The message to be logged
     * @param array $context The context for the message to be logged
     * @return bool
     */
    abstract public function log(int $level, string $message, array $context = array()): bool;
    
}

