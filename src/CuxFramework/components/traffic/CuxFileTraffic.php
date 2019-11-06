<?php

namespace CuxFramework\components\traffic;

use CuxFramework\utils\CuxBase;

class CuxFileTraffic extends CuxTraffic {
    
    public $logFile = false;
    
    public static function config(array $config): void {
        $ref = static::getInstance();
        CuxBase::config($ref, $config);
        
        if (!$ref->logFile){
            $ref->logFile = "log".DIRECTORY_SEPARATOR."traffic_".date("Y-m-d").".log";
        }
    }

    public function logRequest(){  
        
        if ($this->ignoreRequest())
            return;
        
        $t = microtime(true);
        $micro = sprintf("%06d", ($t - floor($t)) * 1000000);
        $d = new \DateTime(date('Y-m-d H:i:s.' . $micro, $t));
        file_put_contents($this->logFile, '[ ' . $d->format("Y-m-d H:i:s.u") . ' ]: ' .json_encode($this->getVisitorsInfo()) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    
}
