<?php

/**
 * CuxIsNumericValidator class file
 * 
 * @package Components
 * @subpackage Validator
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\components\validator;

use CuxFramework\utils\Cux;

/**
 * Simple class that checks if the value of a given property from a given object is of numeric type<br />
 * Predefined $_props keys:<br />
 *     * "allowEmpty" - optional
 */
class CuxIsNumericValidator extends CuxBaseValidator{
    
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
                $obj->addError($attr, Cux::translate("core.errors", "Invalid attribute: {attr}!", array(
                    "{attr}" => $label
                )));
            }
            return false;
        }
        
        $value = is_object($obj) ? $obj->$attr : $obj[$attr];
        
        if ((is_null($value) || empty($value)) && isset($this->_props["allowEmpty"]) && $this->_props["allowEmpty"]){
            return true;
        }
        
        if (!is_numeric($value)){
            if ($canAddError){
                $obj->addError($attr, Cux::translate("core.errors", "{attr} must be numeric!", array(
                    "{attr}" => $label
                )));
            }
            return false;
        }
        return true;
        
    }

}