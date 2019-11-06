<?php

namespace CuxFramework\console;

use CuxFramework\utils\CuxSingleton;

abstract class CuxCommand extends CuxSingleton{
    public static function config(array $config): void {}
    
    abstract public function run(array $args);

}