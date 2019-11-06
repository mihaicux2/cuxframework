<?php

namespace CuxFramework\components\validator;

use CuxFramework\utils\Cux;

class CuxNotEmptyValidator extends CuxBaseValidator{
    
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
                $obj->addError($attr, Cux::translate("error", "Invalid attribute: {attr}!", array(
                    "{attr}" => $label
                )));
            }
            return false;
        }
        
        $value = is_object($obj) ? $obj->$attr : $obj[$attr];
        if (empty($value)){
            if ($canAddError){
                $obj->addError($attr, Cux::translate("error", "{attr} is empty!!", array(
                    "{attr}" => $label
                )));
            }
            return false;
        }
        return true;
        
    }

}