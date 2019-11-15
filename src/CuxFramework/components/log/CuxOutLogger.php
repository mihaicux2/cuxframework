<?php

namespace CuxFramework\components\log;

use CuxFramework\utils\CuxBase;

class CuxOutLogger extends CuxLogger {
    
    public function config(array $config): void {
        parent::config($config);
    }

    public function log(int $level, string $message, array $context = array()): bool{
        if ($this->logLevel & $level){
            switch ($level){
                case self::DEBUG:
                    $logLevel = "debug";
                    break;
                case self::INFO:
                    $logLevel = "info";
                    break;
                case self::NOTICE:
                    $logLevel = "notice";
                    break;
                case self::WARNING:
                    $logLevel = "warning";
                    break;
                case self::ERROR:
                    $logLevel = "error";
                    break;
                case self::CRITICAL:
                    $logLevel = "critical";
                    break;
                case self::ALERT:
                    $logLevel = "alert";
                    break;
                case self::EMERGENCY:
                    $logLevel = "emergency";
                    break;
                default:
                    $logLevel = "other";
            }
            $t = microtime(true);
            $micro = sprintf("%06d", ($t - floor($t)) * 1000000);
            $d = new \DateTime(date('Y-m-d H:i:s.' . $micro, $t));
            echo '[ ' . $d->format("Y-m-d H:i:s.u") . ' ]: <' . $logLevel . '>  ' .$message."<br />";
            if (is_array($context) && !empty($context)){
                print_r($context);
                return true;
            }
            return false;
        }
    }
    
}
