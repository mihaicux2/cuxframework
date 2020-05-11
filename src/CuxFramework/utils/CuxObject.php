<?php

namespace CuxFramework\utils;

use CuxFramework\utils\CuxSlug;

class CuxObject extends CuxBaseObject{

    protected $_attributes = array();
    protected $_errors = array();
    protected $_hasErrors = false;
    
    static protected $_labels;
    
    public function config(array $properties) {
        $this->config($properties);
    }

    public static function className() {
        return get_called_class();
    }
    
    public function __get(string $name){
        $getter = "get".$name;
        if (method_exists($this, $getter)){
            return $this->$getter();
        }
        elseif (property_exists($this, $name)){
            return $this->$name;
        }
        elseif (array_key_exists($name, $this->_attributes)){
            return $this->_attributes[$name];
        }
        $className = get_class($this);
        throw new \Exception(Cux::translate("core.errors", "Undefined property: {class}.{attribute}", array("{class}" => $className, "{attribute}" => $name), "Message shown when trying to access invalid class properties"), 503);
    }
    
    public function __set(string $name, $value){
        $setter = "set" . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        }
        elseif (property_exists($this, $name)) {
            $this->$name = $value;
        }
        elseif (array_key_exists($name, $this->_attributes)){
            $this->_attributes[$name] = $value;
        }
        else{
            $className = get_class($this);
            throw new \Exception(Cux::translate("core.errors", "Undefined property: {class}.{attribute}", array("{class}" => $className, "{attribute}" => $name), "Message shown when trying to access invalid class properties"), 503);
        }
    }
    
    public function __isset(string $name) {
        $getter = "get" . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter() !== null;
        }
        elseif (property_exists($this, $name)) {
            return $this->$name !== null;
        }
        elseif (array_key_exists($name, $this->_attributes)) {
            return $this->_attributes[$name] !== null;
        }
        return false;
    }

    public function __unset(string $name) {
        $setter = "set" . $name;
        if (method_exists($this, $setter)) {
            $this->$setter(null);
        }
        elseif (property_exists($this, $name)) {
            $this->$name = null;
        }
        elseif (isset($this->_attributes[$name])) {
            $this->_attributes[$name] = null;
        }
        else {
            $className = get_class($this);
            throw new \Exception(Cux::translate("core.errors", "Undefined property: {class}.{attribute}", array("{class}" => $className, "{attribute}" => $name), "Message shown when trying to access invalid class properties"), 503);
        }
    }
    
    public function hasAttribute($attribute){
        return array_key_exists($attribute, $this->_attributes);
    }
    
    public function getAttribute($attribute){
        if (isset($this->_attributes[$attribute])){
            return $this->_attributes[$attribute];
        }
        return null;
    }
    
    public function setAttribute(string $attribute, $value){
        if (!$this->hasAttribute($attribute)){
            $className = get_class($this);
            throw new \Exception(Cux::translate("core.errors", "Undefined property: {class}.{attribute}", array("{class}" => $className, "{attribute}" => $name), "Message shown when trying to access invalid class properties"), 503);
        }
        $this->_attributes[$attribute] = $value;
        return $this;
    }
    
    public function getAttributes(){
        return $this->_attributes;
    }
    
    public function setAttributes($attributes){
        if (!is_array($attributes) || empty($attributes)){
            return;
        }
        foreach ($attributes as $key => $value){
            $this->setAttribute($key, $value);
        }
    }
    
    public function addError($field, $message){
        $this->_errors[$field] = $message;
        $this->_hasErrors = true;
    }
    
    public function setErrors($errors) {
        foreach ($errors as $attribute => $error) {
            $this->addError($attribute, $error);
        }
    }
    
    public function clearError($field){
        if ($this->hasError($attribute)) {
            $this->_errors[$attribute] = null;
            unset($this->_errors[$attribute]);
        }
    }
    
    public function clearErrors(){
        $this->_errors = array();
    }
    
    public function getErrors(){
        return $this->_errors;
    }
    
    public function hasError($field){
        return isset($this->_errors[$field]);
    }
    
    public function hasErrors(){
        return $this->_hasErrors;
    }
    
    public function getError($field){
        return $this->hasError($field) ? $this->_errors[$field] : false;
    }

    public function rules(){
        return array();
    }
    
    public function validate(){
        $ret = true;
        $rules = $this->rules();
        if (!empty($rules)){
            foreach ($rules as $rule){
                $validator = new $rule["validator"]();
                if (isset($rule["params"])){
                    $validator->config($rule["params"]);
                }
                foreach ($rule["fields"] as $field){
                    if (!$validator->validate($this, $field)){
                        $ret = false;
                    }
                }
            }
        }
        return $ret;
    }
    
    public function labels(){
        return array();
    }
    
    public function getLabel($field){
        if (!static::$_labels){
            static::$_labels = $this->labels();
        }
        return isset(static::$_labels[$field]) ? static::$_labels[$field] : $this->generateAttributeLabel($field);
    }
    
    public function getAttributeLabel($attribute){
        return $this->getLabel($attribute);
    }
    
    public function getAttributeId($attribute){
//        return CuxSlug::slugify(get_class($this)."_".$attribute);
        return $this->getShortName()."_".$attribute;
    }
    
    public function getAttributeName($attribute){
//        return CuxSlug::slugify(get_class($this))."[".CuxSlug::slugify($attribute)."]";
        return $this->getShortName()."[".$attribute."]";
    }
    
    public function generateAttributeLabel($name) {
        return ucwords(trim(strtolower(str_replace(array('-', '_', '.'), ' ', preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $name)))));
    }
    
    public function compareIgnoreCase($a, $b){
        return strtolower(trim($a)) == strtolower(trim($b));
    }
    
    public function validEmailAddress($address){
        return filter_var($address, FILTER_VALIDATE_EMAIL);
    }
    
}
