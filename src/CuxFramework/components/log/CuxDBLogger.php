<?php

namespace CuxFramework\components\log;

use CuxFramework\utils\CuxBase;
use CuxFramework\utils\Cux;

class CuxDBLogger extends CuxLogger {
    
    public $logTable = "cux_log";
    public $dbConnection = "db";
    
    public function config(array $config) {
        parent::config($config);
    }

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
