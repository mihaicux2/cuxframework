<?php

namespace CuxFramework\components\validator;

use CuxFramework\utils\Cux;

class CuxLengthValidator extends CuxBaseValidator {

    public function validate($obj, string $attr): bool {
        
        if (is_object($obj)) {
            $label = method_exists($obj, "getLabel") ? $obj->getLabel($attr) : $attr;
            $canAddError = method_exists($obj, "addError");
        } else {
            $label = $attr;
            $canAddError = false;
        }

        if (!parent::checkHasProperty($obj, $attr)) {
            if ($canAddError) {
                $obj->addError($attr, Cux::translate("core.errors", "Invalid attribute: {attr}!", array(
                            "{attr}" => $label
                )));
            }
            return false;
        }

        $value = is_object($obj) ? $obj->$attr : $obj[$attr];

        if (!is_string($value)) {
            if ($canAddError) {
                $obj->addError($attr, Cux::translate("core.errors", "{attr} must be string!", array("{attr}" => $label), "Error shown on model validation"));
            }
            return false;
        }

        $len = strlen($value);

        if (isset($this->_props["minLength"]) && isset($this->_props["maxLength"]) && ($len < $this->_props["minLength"] || $len > $this->_props["maxLength"])) {
            if ($len < $this->_props["minLength"] || $len > $this->_props["maxLength"]) {
                if ($canAddError) {
                    $obj->addError($attr, Cux::translate("core.errors", "{attr} must be between {min_len} and {max_len} characters!", array("{attr}" => $label,"{min_len}" => $this->_props["minLength"],"{max_len}" => $this->_props["maxLength"]), "Error shown on model validation"));
                }
                return false;
            }
        } elseif (isset($this->_props["minLength"]) && ($len < $this->_props["minLength"])) {
             if ($canAddError) {
                    $obj->addError($attr, Cux::translate("core.errors", "{attr} must be at least {min_len} characters!", array("{attr}" => $label,"{min_len}" => $this->_props["minLength"]), "Error shown on model validation"));
                }
                return false;
         } elseif (isset($this->_props["maxLength"]) && ($len > $this->_props["maxLength"])) {
             if ($canAddError) {
                    $obj->addError($attr, Cux::translate("core.errors", "{attr} must be at most {max_len} characters!", array("{attr}" => $label,"{max_len}" => $this->_props["maxLength"]), "Error shown on model validation"));
                }
                return false;
         }
         
        return true;
    }

}
