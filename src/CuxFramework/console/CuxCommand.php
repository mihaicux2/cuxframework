<?php

namespace CuxFramework\console;

use CuxFramework\utils\CuxBaseObject;

abstract class CuxCommand extends CuxBaseObject{
    public function config(array $config): void {}
    
    abstract public function run(array $args);

}