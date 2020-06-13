<?php

/**
 * CuxFileTraffic class file
 * 
 * @package Components
 * @subpackage Traffic
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\components\traffic;

use CuxFramework\utils\CuxBase;

/**
 * Simple class that stores the current request details in a file
 */
class CuxFileTraffic extends CuxTraffic {
    
    /**
     * The file that will store the request details
     * @var string
     */
    public $logFile = false;
    
    /**
     * Setup object instance properties
     * @param array $config
     */
    public function config(array $config) {
        parent::config($config);
        
        if (!$this->logFile){
            $this->logFile = "log".DIRECTORY_SEPARATOR."traffic_".date("Y-m-d").".log";
        }
    }

    /**
     * Process/store current request details
     * Saves request details in a file
     */
    public function logRequest(){  
        
        if ($this->ignoreRequest())
            return;
        
        $t = microtime(true);
        $micro = sprintf("%06d", ($t - floor($t)) * 1000000);
        $d = new \DateTime(date('Y-m-d H:i:s.' . $micro, $t));
        file_put_contents($this->logFile, '[ ' . $d->format("Y-m-d H:i:s.u") . ' ]: ' .json_encode($this->getVisitorsInfo()) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    
}
