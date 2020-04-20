<?php

namespace CuxFramework\components\validator;

use CuxFramework\utils\CuxBaseObject;

abstract class CuxBaseValidator extends CuxBaseObject{
    
    protected $_props;
    
    abstract function validate($obj, string $attr): bool;
    
    public function config(array $config) {
        $this->_props = $config;
    }
    
    protected function checkHasProperty($obj, string $attr): bool{
        if (is_object($obj)){
            $ret = false;
            if (method_exists($obj, "hasAttribute")){
                $ret = $obj->hasAttribute($attr);
            }
            return $ret ? $ret : (property_exists($obj, $attr) || isset($obj->$attr));
        }
        if (is_array($obj)){
            return isset($obj[$attr]);
        }
    }
    
}