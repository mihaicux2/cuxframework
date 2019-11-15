<?php

namespace CuxFramework\components\traffic;

use CuxFramework\utils\CuxBase;

class CuxNullTraffic extends CuxTraffic {
    
    public function config(array $config): void {
        parent::config($config);
    }

    public function logRequest() {}

}