<?php

namespace CuxFramework\components\validator;

use CuxFramework\utils\Cux;

class CuxMatchAttrValidator extends CuxBaseValidator{
    
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
        if (!parent::checkHasProperty($obj, $this->_props["field"])){
            if ($canAddError){
                $label2 = method_exists($obj, "getLabel") ? $obj->getLabel($this->_props["field"]) : $this->_props["field"];
                $obj->addError($this->_props["field"], Cux::translate("core.errors", "Invalid attribute: {attr}!", array( "{attr}" => $label2), "Error shown on model validation"));
            }
            return false;
        }
        
        if (!isset($label2)) {
            $label2 = $this->_props["field"];
        }
        
        $value = is_object($obj) ? $obj->$attr : $obj[$attr];
        $value2 = is_object($obj) ? $obj->{$this->_props["field"]} : $obj[$this->_props["field"]];
        if ($value != $value2){
            if ($canAddError){
                $obj->addError($attr, Cux::translate("core.errors", "{attr} must match {attr2}", array("{attr}" => $label,"{attr2}" => $label2), "Error shown on model validation"));
            }
            return false;
        }
        return true;
        
    }

}