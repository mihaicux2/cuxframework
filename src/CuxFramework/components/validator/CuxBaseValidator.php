<?php

namespace CuxFramework\components\validator;

use CuxFramework\utils\CuxSingleton;

abstract class CuxBaseValidator extends CuxSingleton{
    
    protected $_props;
    
    abstract function validate($obj, string $attr): bool;
    
    public static function config(array $config): void {
        static::getInstance()->_props = $config;
    }
    
    protected function checkHasProperty($obj, string $attr): bool{
        if (is_object($obj)){
            return (property_exists($obj, $attr) || isset($obj->$attr));
        }
        if (is_array($obj)){
            return isset($obj[$attr]);
        }
    }
    
}