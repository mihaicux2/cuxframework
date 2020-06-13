<?php

/**
 * CuxDBLogger class file
 * 
 * @package Components
 * @subpackage Log
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\components\log;

use CuxFramework\utils\CuxBase;
use CuxFramework\utils\Cux;

/**
 * PSR-4 logging class that stores messages in the database
 * In order to use this component, the DataBase needs to have table with the following structure:
 *     * log_time  - DATETIME, NOT NULL
 *     * level - INT(3), NOT NULL
 *     * message - TEXT, NOT NULL
 *     * context - TEXT, NOT NULL
 */
class CuxDBLogger extends CuxLogger {
    
    /**
     * The table that will store the log messages
     * @var string
     */
    public $logTable = "cux_log";
    
    /**
     * The framework component that handles the DB connection
     * @var string
     */
    public $dbConnection = "db";
    
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
    public function log(int $level, string $message, array $context = array()): bool{
        if ($this->logLevel & $level){
            $t = microtime(true);
            $micro = sprintf("%06d", ($t - floor($t)) * 1000000);
            $d = new \DateTime(date('Y-m-d H:i:s.' . $micro, $t));
            
            $tableName = Cux::getInstance()->{$this->dbConnection}->quoteTableName($this->logTable);
            $dbName = Cux::getInstance()->{$this->dbConnection}->quoteTableName(Cux::getInstance()->{$this->dbConnection}->getDBName());
            
            $sql = "INSERT INTO {$dbName}.{$tableName} (log_time, level, message, context)
                    VALUES (:log_time, :level, :message, :context)";
            $stmt = Cux::getInstance()->{$this->dbConnection}->prepare($sql);
            $stmt->bindValue(":log_time", $d->format("Y-m-d H:i:s") );
            $stmt->bindValue(":level", $level);
            $stmt->bindValue(":message", $message );
            $stmt->bindValue(":context", json_encode($context) );
            
            return $stmt->execute();
        }
        return false;
    }
    
}
