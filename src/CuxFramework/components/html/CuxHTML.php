<?php

namespace CuxFramework\components\html;

use CuxFramework\utils\CuxBaseObject;
use CuxFramework\utils\CuxObject;
use CuxFramework\utils\CuxSlug;
use CuxFramework\utils\Cux;

class CuxHTML extends CuxBaseObject {

    /**
     * List of attributes that need special attention when their values are arrays
     * @var array
     */
    public static $specialAttributes = [
        "ng" => 1,
        "data-ng" => 1,
        "data" => 1
    ];
    public static $selfClosingTags  = [
        "link" => 1,
        "input" => 1,
        "hr" => 1,
        "br" => 1,
        "img" => 1,
        "meta" => 1,
        "base" => 1,
        "area" => 1,
        "link" => 1,
        "embed" => 1,
        "command" => 1,
        "param" => 1,
        "source" => 1,
        "wbr" => 1,
        "track" => 1
    ];

    public function config(array $config) {
        parent::config($config);
    }

    public static function encode($content, $doubleEncode = true) {
        return htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, Cux::getInstance() ? Cux::getInstance()->charset : "UTF-8", $doubleEncode);
    }
    
    public static function decode($content){
        return htmlspecialchars_decode($content, ENT_QUOTES);
    }

    public static function renderAttributes(array $attributes) {
        if (empty($attributes)) return "";
        $ret = '';
        foreach ($attributes as $key => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $ret .= " $key";
                }
            } elseif (is_array($value)) {
                if (in_array($key, static::$specialAttributes)) {
                    foreach ($value as $n => $v) {
                        if (is_array($v)) {
                            $ret .= " {$key}-{$n}='" . json_encode($v, JSON_HEX_APOS) . "'";
                        } else {
                            $ret .= " {$key}-{$n}=\"" . static::encode($v) . '"';
                        }
                    }
                } else {
                    $ret .= " {$key}='" . json_encode($value, JSON_HEX_APOS) . "'";
                }
            } elseif ($value !== null) {
                $ret .= " {$key}=\"" . static::encode($value) . '"';
            }
        }

        return $ret;
    }

    public static function tag(string $tagName, string $content = '', array $props = array()){
        
        $prefix = "";
        $suffix = "";
        if (isset($props["prefix"])) {
            $prefix = $props["prefix"];
            $props["prefix"] = null;
            unset($props["prefix"]);
        }
        if (isset($props["suffix"])) {
            $suffix = $props["suffix"];
            $props["suffix"] = null;
            unset($props["suffix"]);
        }
//        if (isset($props["name"]) && !isset($props["id"])){
//            $props["id"] = CuxSlug::slugify($props["name"], "_", false);
//        }
        
        return $prefix."<".$tagName.static::renderAttributes($props).(isset(static::$selfClosingTags[strtolower($tagName)]) ? " />" : ">{$content}</{$tagName}>").$suffix;
    }
    
    public static function beginTag(string $tagName, array $props = array()) {
        return "<".$tagName.static::renderAttributes($props).">";
    }

    public static function endTag(string $tagName) {
        return "</{$tagName}>";
    }
    
    public static function style(string $content, array $props = array()){
        return static::tag("style", $content, $props);
    }
    
    public static function script(string $content, array $props = array()){
        return static::tag("script", $content, $props);
    }
    
    public static function cssFile(string $url, array $props = array()){
        if (!isset($props["rel"])){
            $props["rel"] = "stylesheet";
        }
        $props["href"] = $url;
        
        if (isset($props["noscript"]) && $props["noscript"] == true){
            $props['noscript'] = null;
            unset($props['noscript']);
            return "<noscript>".static::tag("link", "", $props)."</noscript>";
        }
        elseif (isset($props["condition"])){
            $condition = $props["condition"];
            $props["condition"] = null;
            unset($props["condition"]);
            return "<!--[if $condition]>".static::tag("link", "", $props)."<![endif]-->";
        } else {
            return static::tag("link", "", $props);
        }
    }
    
    public static function jsFile(string $url, array $props = array()){
        if (!isset($props["type"])){
            $props["type"] = "text/javascript";
        }
        $props["src"] = $url;
        
        if (isset($props["noscript"]) && $props["noscript"] == true){
            $props['noscript'] = null;
            unset($props['noscript']);
            return "<noscript>".static::tag("script", "", $props)."</noscript>";
        }
        elseif (isset($props["condition"])){
            $condition = $props["condition"];
            $props["condition"] = null;
            unset($props["condition"]);
            return "<!--[if $condition]>".static::tag("script", "", $props)."<![endif]-->";
        } else {
            return static::tag("script", "", $props);
        }
    }
    
    public static function beginForm(string $action = "", string $method = "POST", array $props = array()){
        $props["method"] = $method;
        $props["action"] = $action;
        return static::tag("form", "", $props);
    }
    
    public static function endForm(){
        return static::endTag("form");
    }
    
    public static function img(string $src, array $props = array()){
        if (!isset($props["alt"])){
            $props["alt"] = "";
        }
        return static::tag("img", "", $props);
    }
    
    public static function a(string $content, string $url = "", array $props = array()){
        $props["href"] = $url;
        return static::tag("a", $content, $props);
    }
    
    public static function mailto(string $content, string $email = "", array $props = array()){
        return static::a($content, "mailto:".$email, $props);
    }
    
    public static function label(string $content, string $for = "", $props = array()){
        if ($for){
            $props["for"] = $for;
        }
        return static::tag("label", $content, $props);
    }
    
    public static function input(string $type, string $name = "", $value = "", array $props = array()) {
        $props["name"] = $name;
        $props["value"] = $value;
        
        if (!isset($props["type"])) {
            $props["type"] = $type;
        }
        
        if (!isset($props["id"])) {
            $props["id"] = CuxSlug::slugify($name);
        }
        
        return static::tag("input", "", $props);
    }
    
    public static function buttonInput(string $name ="", $value = "", array $props = array()){
        return static::input("button", $name, $value, $props);
    }
    
    public static function submitInput(string $name ="", $value = "", array $props = array()){
        return static::input("submit", $name, $value, $props);
    }
    
    public static function resetInput(string $name ="", $value = "", array $props = array()){
        return static::input("reset", $name, $value, $props);
    }

    public static function textInput(string $name, string $value = "", array $props = array()) {
        return static::input("text", $name, $value, $props);
    }

    public static function passwordInput(string $name, string $value="", array $props = array()) {
        return static::input("password", $name, $value, $props);
    }
    
    public static function hiddentInput(string $name, string $value = "", array $props = array()) {
        return static::input("hidden", $name, $value, $props);
    }
    
    public static function fileInput(string $name, string $value = "", array $props = array()) {
        return static::input("file", $name, $value, $props);
    }
    
    public static function button(string $content = "", array $props = array()){
        if (!isset($props["type"])){
            $props["type"] = "button";
        }
        return static::tag("button", $content, $props);
    }
    
    public static function submitButton(string $content = "Submit", array $props = array()){
        $props["type"] = "submit";
        return static::button($content, $props);
    }
    
    public static function resetButton(string $content = "Reset", array $props = array()){
        $props["type"] = "reset";
        return static::button($content, $props);
    }

    public static function textarea(string $name, $value, $props = array()) {
        $props["name"] = $name;
        return static::tag("textarea", static::encode($value), $props);
    }

    public static function radio(string $name, $value, bool $checked = false, $props = array()) {
        if ($checked) {
            $props["checked"] = "checked";
        }
        
        if (!$props["id"]) {
            $props["id"] = CuxSlug::slugify($name);
        }
        
        if (!isset($props["pattern"])) {
            $props["pattern"] = array(
                "input",
                "label"
            );
        }
        
        if (isset($props["label"])){
            $label = $props["label"];
            $props["label"] = null;
            unset($props["label"]);
        } else {
            $label = "";
        }
        
        if (isset($props["labelProps"])){
            $labelProps = $props["labelProps"];
            $props["labelProps"] = null;
            unset($props["labelProps"]);
        } else {
            $labelProps = array();
        }
        
        $patternArr = $props["pattern"];
        $props["pattern"] = null;
        unset($props["pattern"]);
        
        $ret = "";
        
        foreach ($patternArr as $pattern) {
            switch ($pattern) {
                case "input":
                    $ret .= static::input("radio", $name, $value, $props);
                    break;
                case "label":
                    if ($label){
                        $ret .= static::label($label, $props["id"], $labelProps);
                    }
                    break;
                default:
                    $ret .= $pattern;
            }
        }
        return $ret;
    }

    public static function checkbox(string $name, $value, bool $checked = false, $props = array()) {
        if ($checked) {
            $props["checked"] = "checked";
        }
        
        if (!$props["id"]) {
            $props["id"] = CuxSlug::slugify($name);
        }
        
        if (!isset($props["pattern"])) {
            $props["pattern"] = array(
                "input",
                "label"
            );
        }
        
        if (isset($props["label"])){
            $label = $props["label"];
            $props["label"] = null;
            unset($props["label"]);
        } else {
            $label = "";
        }
        
        if (isset($props["labelProps"])){
            $labelProps = $props["labelProps"];
            $props["labelProps"] = null;
            unset($props["labelProps"]);
        } else {
            $labelProps = array();
        }
        
        $patternArr = $props["pattern"];
        $props["pattern"] = null;
        unset($props["pattern"]);
        
        $ret = "";
        
        foreach ($patternArr as $pattern) {
            switch ($pattern) {
                case "input":
                    $ret .= static::input("checkbox", $name, $value, $props);
                    break;
                case "label":
                    if ($label){
                        $ret .= static::label($label, $props["id"], $labelProps);
                    }
                    break;
                default:
                    $ret .= $pattern;
            }
        }
        return $ret;
    }

    public static function dropdownOption(array $item, bool $selected = false, $props = array()) {
        if ($selected) {
            $props["selected"] = "selected";
        }
        $props["value"] = $item["key"];
        
        return static::tag("option", $item["value"], $props);
    }

    public static function radioboxList(string $name, $value, array $elements, $props = array()) {
        $ret = "";
        $prefix = "";
        $suffix = "";
        $props["name"] = $name;
        if (isset($props["prefix"])) {
            $prefix = $props["prefix"];
            $props["prefix"] = null;
            unset($props["prefix"]);
        }
        if (isset($props["suffix"])) {
            $suffix = $props["suffix"];
            $props["suffix"] = null;
            unset($props["suffix"]);
        }
        $ret = $prefix;

        foreach ($elements as $key => $val) {
            $itemProps = $props;
            $itemProps["id"] = CuxSlug::slugify($name . "_" . $key);
            $itemProps["label"] = $val;
            $itemProps["for"] = $itemProps["id"];
            $ret .= static::radio($name, $val, ($value == $key), $itemProps);
        }

        $ret .= $suffix;

        return $ret;
    }

    public static function checkboxList(string $name, $value, array $elements, $props = array()) {
        $ret = "";
        $prefix = "";
        $suffix = "";
        $props["name"] = $name;
        if (isset($props["prefix"])) {
            $prefix = $props["prefix"];
            $props["prefix"] = null;
            unset($props["prefix"]);
        }
        if (isset($props["suffix"])) {
            $suffix = $props["suffix"];
            $props["suffix"] = null;
            unset($props["suffix"]);
        }
        $ret = $prefix;
        
        $checkboxName = $name;
        if (substr($checkboxName, -1) != "]"){
            $checkboxName .= "[]";
        }

        foreach ($elements as $key => $val) {
            $itemProps = $props;
            $itemProps["id"] = CuxSlug::slugify($name . "_" . $key);
            $itemProps["label"] = $val;
            $itemProps["for"] = $itemProps["id"];
            $ret .= static::checkbox($checkboxName, $val, ($value == $key), $itemProps);
        }

        $ret .= $suffix;

        return $ret;
    }

    public static function dropdownOptionGroup(string $name, array $elements, $value, $props = array()) {
        $ret = static::beginTag("optgroup", array(
                    "label" => $name
        ));

        foreach ($elements as $key => $val) {
            $ret .= static::dropdownOption(array(
                        "key" => $key,
                        "value" => $val,
                            ), ($value == $key), $props);
        }

        $ret .= static::endTag("optgroup");

        return $ret;
    }

    public static function dropdownList(string $name, $value, array $elements, $props = array()) {
        $ret = "";
        $prefix = "";
        $suffix = "";
        $props["name"] = $name;
        if (isset($props["prefix"])) {
            $prefix = $props["prefix"];
            $props["prefix"] = null;
            unset($props["prefix"]);
        }
        if (isset($props["suffix"])) {
            $suffix = $props["suffix"];
            $props["suffix"] = null;
            unset($props["suffix"]);
        }
        $ret = $prefix;

        $ret .= static::beginTag("select", $props);

        foreach ($elements as $key => $val) {
            if (is_array($val)) {
                $ret .= static::dropdownOptionGroup($key, $val, $value, $props);
            } else {
                $ret .= static::dropdownOption(array(
                            "key" => $key,
                            "value" => $val,
                                ), ($value == $key), $props);
            }
        }

        $ret .= static::endTag("select");

        $ret .= $suffix;

        return $ret;
    }
    
    public static function activeLabel(CuxObject $model, string $attribute, array $props = array()){
        
        if (isset($props["label"])){
            $label = $props["label"];
            $props["label"] = null;
            unset($props["label"]);
        } else {
            $label = $model->getAttributeLabel($attribute);
        }
        
        if (isset($props["for"])){
            $for = $props["for"];
            $props["for"] = null;
            unset($props["for"]);
        } else {
            $for = $model->getAttributeId($attribute);
        }
        
        return static::label($label, $for, $props);        
        
    }
    
    public static function activeHiddenInput(CuxObject $model, string $attribute, array $props = array()){
        return static::hiddentInput($model->getAttributeName($attribute), $model->getAttribute($attribute), $props);
    }
    
    public static function activeTextInput(CuxObject $model, string $attribute, array $props = array()){   
        if (!isset($props["id"])){
            $props["id"] = $model->getAttributeId($attribute);
        }
        return static::textInput($model->getAttributeName($attribute), $model->getAttribute($attribute), $props);
    }
    
    public static function activeTextarea(CuxObject $model, string $attribute, array $props = array()){
        if (!isset($props["id"])){
            $props["id"] = $model->getAttributeId($attribute);
        }
        return static::textarea($model->getAttributeName($attribute), $model->getAttribute($attribute), $props);
    }
    
    public static function activeCheckboxlist(CuxObject $model, string $attribute, array $elements = array(), array $props = array()){
        if (!isset($props["id"])){
            $props["id"] = $model->getAttributeId($attribute);
        }
        return static::checkboxList($model->getAttributeName($attribute), $model->getAttribute($attribute), $elements, $props);
    }
    
    public static function activeRadioboxList(CuxObject $model, string $attribute, array $elements = array(), array $props = array()){
        if (!isset($props["id"])){
            $props["id"] = $model->getAttributeId($attribute);
        }
        return static::radioboxList($model->getAttributeName($attribute), $model->getAttribute($attribute), $elements, $props);
    }
    
    public static function activeDropdownList(CuxObject $model, string $attribute, array $elements = array(), array $props = array()){
        if (!isset($props["id"])){
            $props["id"] = $model->getAttributeId($attribute);
        }
        return static::dropdownList($model->getAttributeName($attribute), $model->getAttribute($attribute), $elements, $props);
    }
    
    public static function modelsToArray(array $list, $key, $val){
        $ret = array();
        foreach ($list as $item){
            $ret[$item->getAttribute($key)] = $item->getAttribute($val);
        }
        return $ret;        
    }

}
