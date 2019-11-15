<?php

namespace CuxFramework\components\traffic;

use CuxFramework\utils\CuxBase;

class CuxOutTraffic extends CuxTraffic {

    public function config(array $config): void {
        parent::config($config);
    }

    public function logRequest() {
        
        if ($this->ignoreRequest())
            return;
        
        print_r($this->getVisitorsInfo());
    }

}
