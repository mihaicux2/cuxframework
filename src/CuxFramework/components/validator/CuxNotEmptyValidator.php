<?php

/**
 * CuxNotEmptyValidator class file
 */

namespace CuxFramework\components\validator;

use CuxFramework\utils\Cux;

/**
 * Simple class that checks if a property of a given object is empty
 */
class CuxNotEmptyValidator extends CuxBaseValidator{
    
    /**
     * Validate a given attribute from the given object instance
     * @param mixed $obj The object to be validated
     * @param string $attr The name of the attribute to be validated
     * @return bool True if the validation test passed
     */
    public function validate($obj, string $attr): bool {
        
        if (is_object($obj)){
            $label = method_exists($obj, "getLabel") ? $obj->getLabel($attr) : $attr;
            $canAddError = method_exists($obj, "addError");
        } else {
            $label = $attr;
            $canAddError = false;
        }
        
        if (!parent::checkHasProperty($obj, $attr)){
            if ($canAddError){
                $obj->addError($attr, Cux::translate("core.errors", "Invalid attribute: {attr}!", array("{attr}" => $label), "Error shown on model validation"));
            }
            return false;
        }
        
        $value = is_object($obj) ? $obj->$attr : $obj[$attr];
        if (empty($value)){
            if ($canAddError){
                $obj->addError($attr, Cux::translate("core.errors", "{attr} is empty!", array( "{attr}" => $label), "Error shown on model validation"));
            }
            return false;
        }
        return true;
        
    }

}