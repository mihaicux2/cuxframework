<?php

/**
 * CuxNullLogger class
 * 
 * @package Components
 * @subpackage Log
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\components\log;

use CuxFramework\utils\CuxBase;

/**
 * Dummy class that doesn't store the log messages
 */
class CuxNullLogger extends CuxLogger {

    /**
     * Setup object instance properties
     * @param array $config
     */
    public function config(array $config) {
        parent::config($config);
    }

    /**
     * 
     * Log a message with a given logging level
     * @param int $level The logging level
     * @param string $message The message to be logged
     * @param array $context The context for the message to be logged
     * @return bool
     */
    public function log(int $level, string $message, array $context = array()): bool {
        return true;
    }

}
