<?php

/**
 * CuxDBTraffic class file
 * 
 * @package Components
 * @subpackage Traffic
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\components\traffic;

use CuxFramework\utils\Cux;
use CuxFramework\models\WebTraffic;

/**
 * Simple class that stores the current request details in the database
 */
class CuxDBTraffic extends CuxTraffic {

    /**
     * Process/store current request details
     * Saves request details in the database
     */
    public function logRequest(){
        
        if ($this->ignoreRequest())
            return;
        
        $visitorsInfo = $this->getVisitorsInfo();
        $visitorsInfo["request_get_data"] = json_encode($visitorsInfo["request_get_data"]);
        $visitorsInfo["request_post_data"] = json_encode($visitorsInfo["request_post_data"]);
        
        $trafficRequest = new WebTraffic();
        $trafficRequest->setAttributes($visitorsInfo);
        $trafficRequest->save();
    }
    
}