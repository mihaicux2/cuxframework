<?php

/**
 * CuxObject class file
 */

namespace CuxFramework\utils;

use CuxFramework\utils\CuxSlug;

/**
 * Class used as a starting point for all of the framework's components
 */
class CuxObject extends CuxBaseObject{

    /**
     * The list of public accessible component attributes
     * @var array
     */
    protected $_attributes = array();
    
    /**
     * The list of errors, mapped using the public accessible component attributes
     * @var array
     */
    protected $_errors = array();
    
    /**
     * Checks if the current instance of CuxObject has errors for any of the public accessible component attributes
     * @var bool
     */
    protected $_hasErrors = false;
    
    /**
     * The list of labels, mapped using the public accessible component attributes
     * @var array
     */
    static protected $_labels;
    
    /**
     * Setup the class attributes
     * @param array $properties
     */
    public function config(array $properties) {
        $this->config($properties);
    }

    /**
     * Get the current object instance's class name
     * @return string
     */
    public static function className(): string {
        return get_called_class();
    }
    
    /**
     * Magic getter for the public accessible component attributes
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
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
    
    /**
     * Magic setter for the public accessible component attributes
     * @param string $name
     * @param mixed $value
     * @throws \Exception
     */
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
    
    /**
     *  Magic "check" method for the public accessible component attributes
     * @param string $name
     * @return boolean
     */
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

    /**
     * Magic "delete" method for the public accessible component attributes
     * @param string $name
     * @throws \Exception
     */
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
    
    /**
     * Checks if the class contains the given attribute
     * @param string $attribute
     * @return bool
     */
    public function hasAttribute(string $attribute): bool{
        return array_key_exists($attribute, $this->_attributes);
    }
    
    /**
     * Get the attribute, using the public accessible component attributes
     * @param string $attribute
     * @return mixed
     */
    public function getAttribute(string $attribute){
        if (isset($this->_attributes[$attribute])){
            return $this->_attributes[$attribute];
        }
        return null;
    }
    
    /**
     * Setter for the public accessible component attributes
     * @param string $attribute
     * @param mixed $value
     * @return \CuxFramework\utils\CuxObject
     * @throws \Exception
     */
    public function setAttribute(string $attribute, $value): CuxObject{
        if (!$this->hasAttribute($attribute)){
            $className = get_class($this);
            throw new \Exception(Cux::translate("core.errors", "Undefined property: {class}.{attribute}", array("{class}" => $className, "{attribute}" => $name), "Message shown when trying to access invalid class properties"), 503);
        }
        $this->_attributes[$attribute] = $value;
        return $this;
    }
    
    /**
     * Multiple getter for the public accessible component attributes
     * @return array
     */
    public function getAttributes(): array{
        return $this->_attributes;
    }
    
    /**
     * Multiple setter for the public accessible component attributes
     * @param type $attributes
     * @return type
     */
    public function setAttributes($attributes){
        if (!is_array($attributes) || empty($attributes)){
            return;
        }
        foreach ($attributes as $key => $value){
            $this->setAttribute($key, $value);
        }
    }
    
    /**
     * Sets an error for a given attribute
     * @param string $field
     * @param string $message
     */
    public function addError(string $field, string $message){
        $this->_errors[$field] = $message;
        $this->_hasErrors = true;
    }
    
    /**
     * Multiple error setter
     * @param type $errors
     */
    public function setErrors(array $errors) {
        foreach ($errors as $attribute => $error) {
            $this->addError($attribute, $error);
        }
    }
    
    /**
     * Remove errors for a given attribute
     * @param type $field
     */
    public function clearError(string $field){
        if ($this->hasError($attribute)) {
            $this->_errors[$attribute] = null;
            unset($this->_errors[$attribute]);
        }
    }
    
    /**
     * Removes all the errors
     */
    public function clearErrors(){
        $this->_errors = array();
    }
    
    /**
     * Multiple getter for the class instance errors
     * @return array
     */
    public function getErrors(): array{
        return $this->_errors;
    }
    
    /**
     * Checks whether a given attribute has errors
     * @param string $field
     * @return bool
     */
    public function hasError(string $field): bool{
        return isset($this->_errors[$field]);
    }
    
    /**
     * Checks if any attribute has errors
     * @return bool
     */
    public function hasErrors(): bool{
        return $this->_hasErrors;
    }
    
    /**
     * Getter for a given attribute errors
     * @param type $field
     * @return mixed
     */
    public function getError(string $field){
        return $this->hasError($field) ? $this->_errors[$field] : false;
    }

    /**
     * Use this method to setup validation rules for the public accessible component attributes
     * @return array
     */
    public function rules(): array{
        return array();
    }
    
    /**
     * Validate a list/all public accessible component attributes
     * @param array $fields
     * @return bool
     */
    public function validate(array $fields = array()): bool{
        $fields = array_flip($fields);
        $ret = true;
        $rules = $this->rules();
        if (!empty($rules)){
            foreach ($rules as $rule){
                $validator = new $rule["validator"]();
                if (isset($rule["params"])){
                    $validator->config($rule["params"]);
                }
                foreach ($rule["fields"] as $field){
                    if ((empty($fields) || isset($fields[$field])) && !$validator->validate($this, $field)){
                        $ret = false;
                    }
                }
            }
        }
        return $ret;
    }
    
    /**
     * Use this method to setup labels for the public accessible component attributes
     * @return array
     */
    public function labels(): array{
        return array();
    }
    
    /**
     * Get the label for a given attribute
     * @param string $field
     * @return string
     */
    public function getLabel(string $field): string{
        if (!static::$_labels){
            static::$_labels = $this->labels();
        }
        return isset(static::$_labels[$field]) ? static::$_labels[$field] : $this->generateAttributeLabel($field);
    }
    
    /**
     * Alias for the "getLabel" method
     * @param string $attribute
     * @return string
     */
    public function getAttributeLabel(string $attribute): string{
        return $this->getLabel($attribute);
    }
    
    /**
     * Generate a valid HTML id string for a given attribute
     * @param string $attribute
     * @return string
     */
    public function getAttributeId(string $attribute): string{
//        return CuxSlug::slugify(get_class($this)."_".$attribute);
        return $this->getShortName()."_".$attribute;
    }
    
    /**
     * Generate a valid HTML name string for a given attribute
     * @param string $attribute
     * @return string
     */
    public function getAttributeName(string $attribute): string{
//        return CuxSlug::slugify(get_class($this))."[".CuxSlug::slugify($attribute)."]";
        return $this->getShortName()."[".$attribute."]";
    }
    
    /**
     * Generate a valid name for a given label
     * @param string $name
     * @return string
     */
    public function generateAttributeLabel(string $name): string {
        return ucwords(trim(strtolower(str_replace(array('-', '_', '.'), ' ', preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $name)))));
    }
    
    /**
     * Compare two strings, case-insensitive
     * @param string $a
     * @param string $b
     * @return bool
     */
    public function compareIgnoreCase(string $a, string $b): bool{
        return strtolower(trim($a)) == strtolower(trim($b));
    }
    
    /**
     * Checks if a given string is a valid email address
     * @param string $address
     * @return mixed
     */
    public function validEmailAddress(string $address){
        return filter_var($address, FILTER_VALIDATE_EMAIL);
    }
    
}
