<?php

namespace CuxFramework\components\log;

use CuxFramework\utils\CuxBase;

class CuxNullLogger extends CuxLogger {

    public static function config(array $config): void {
        $ref = static::getInstance();
        CuxBase::config($ref, $config);
    }

    public function log(int $level, string $message, array $context = array()): bool {
        return true;
    }

}
