<?php

namespace CuxFramework\components\log;

use CuxFramework\utils\CuxBase;

class CuxNullLogger extends CuxLogger {

    public function config(array $config) {
        parent::config($config);
    }

    public function log(int $level, string $message, array $context = array()): bool {
        return true;
    }

}
