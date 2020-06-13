<?php

/**
 * CuxUniqueValidator class file
 * 
 * @package Components
 * @subpackage Validator
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\components\validator;

use CuxFramework\utils\Cux;
use CuxFramework\components\db\CuxDBCriteria;

/**
 * Simple class that checks if the given property of a given object is already present in the database
 */
class CuxUniqueValidator extends CuxBaseValidator{
    
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
        
        if (is_object($obj) && is_subclass_of($obj, "CuxFramework\components\db\CuxDBObject")){
            $crit = new CuxDBCriteria();
            $crit->addCondition("$attr=:val");
            $crit->params[":val"] = $value;
            $similarFound = $obj->countAllByCondition($crit);
            
            if ($similarFound > 0){
                if ($canAddError){
                    $obj->addError($attr, Cux::translate("core.errors", "{attr} is not unique!", array( "{attr}" => $label), "Error shown on model validation"));
                }
                return false;
            }
        }
        
        return true;
        
    }

}