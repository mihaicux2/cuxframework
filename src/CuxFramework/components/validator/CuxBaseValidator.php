<?php

/**
 * CuxBaseValidator abstract class file
 */

namespace CuxFramework\components\validator;

use CuxFramework\utils\CuxBaseObject;

/**
 * Abstract class to be used as a starting point for object validation
 */
abstract class CuxBaseValidator extends CuxBaseObject{
    
    /**
     * The list of properties specific for each validator
     * @var array 
     */
    protected $_props;
    
    /**
     * Validate a given attribute from the given object instance
     * @param mixed $obj The object to be validated
     * @param string $attr The name of the attribute to be validated
     * @return bool True if the validation test passed
     */
    abstract function validate($obj, string $attr): bool;
    
    /**
     * Setup the list of properties
     * @param array $config
     */
    public function config(array $config) {
        $this->_props = $config;
    }
    
    /**
     * Check if the given object has the specified attribute
     * @param type $obj The object to be tested
     * @param string $attr The attribute to be checked
     * @return bool True if the given object has the specified attribute
     */
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