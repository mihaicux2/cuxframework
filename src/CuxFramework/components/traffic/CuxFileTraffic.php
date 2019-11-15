<?php

namespace CuxFramework\components\traffic;

use CuxFramework\utils\CuxBase;

class CuxFileTraffic extends CuxTraffic {
    
    public $logFile = false;
    
    public function config(array $config): void {
        parent::config($config);
        
        if (!$this->logFile){
            $this->logFile = "log".DIRECTORY_SEPARATOR."traffic_".date("Y-m-d").".log";
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
