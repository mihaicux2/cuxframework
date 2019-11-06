<?php

namespace CuxFramework\components\traffic;

use CuxFramework\utils\Cux;
use CuxFramework\models\WebTraffic;

class CuxDBTraffic extends CuxTraffic {

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