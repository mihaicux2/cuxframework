<?php

namespace CuxFramework\components\traffic;

use CuxFramework\utils\CuxBase;

class CuxNullTraffic extends CuxTraffic {
    
    public static function config(array $config): void {
        $ref = static::getInstance();
        CuxBase::config($ref, $config);
    }

    public function logRequest() {}

}