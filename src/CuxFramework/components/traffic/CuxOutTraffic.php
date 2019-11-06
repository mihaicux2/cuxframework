<?php

namespace CuxFramework\components\traffic;

use CuxFramework\utils\CuxBase;

class CuxOutTraffic extends CuxTraffic {

    public static function config(array $config): void {
        $ref = static::getInstance();
        CuxBase::config($ref, $config);
    }

    public function logRequest() {
        
        if ($this->ignoreRequest())
            return;
        
        print_r($this->getVisitorsInfo());
    }

}
