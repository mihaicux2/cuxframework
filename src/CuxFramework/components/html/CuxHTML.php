<?php

namespace CuxFramework\components\html;

use CuxFramework\utils\CuxBaseObject;
use CuxFramework\utils\CuxObject;
use CuxFramework\utils\CuxSlug;
use CuxFramework\utils\Cux;

/**
 * Simple class used to generate HTML code directly form PHP
 */
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

    /**
     * List of tag names that are self closing (ie. no need for an extra ending tag: <input type="hidden" name="demoInput" value="1" />)
     * @var array
     */
    public static $selfClosingTags = [
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

    /**
     * Encodes a string to be HTML readable
     * @param string $content The HTML content to be encoded
     * @param bool $doubleEncode Encode existing HTML entities
     * @return string
     */
    public static function encode(string $content, bool $doubleEncode = true): string {
        return htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, Cux::getInstance() ? Cux::getInstance()->charset : "UTF-8", $doubleEncode);
    }

    /**
     * Decodes a HTML encoded string
     * @param string $content The HTML content to be decoded
     * @return string
     */
    public static function decode(string $content): string {
        return htmlspecialchars_decode($content, ENT_QUOTES);
    }

    /**
     * Renders the list of attributes as HTML code
     * @param array $attributes The list of HTML attributes
     * @return string
     */
    public static function renderAttributes(array $attributes): string {
        if (empty($attributes))
            return "";
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

    /**
     * Creates an HTML tag with given name, content and attributes
     * Special parameters "suffix" and "prefix" can be granted and will be part (start / end) of the generated output
     * @param string $tagName The HTML tag to be generated
     * @param string $content The content enclosed within the HTML tag
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function tag(string $tagName, string $content = '', array $props = array()): string {

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
        if (isset($props["name"]) && !isset($props["id"])) {
            $props["id"] = CuxSlug::slugify($props["name"], "_", false);
        }

        if ($tagName == "option") {
            if (isset($props["id"])) {
                unset($props["id"]);
            }
            if (isset($props["name"])) {
                unset($props["name"]);
            }
        }

        return $prefix . "<" . $tagName . static::renderAttributes($props) . ( isset(static::$selfClosingTags[strtolower($tagName)]) ? " />" : ">{$content}</{$tagName}>") . $suffix;
    }

    /**
     * Opens an HTML tag
     * @param string $tagName The HTML tag to be generated
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function beginTag(string $tagName, array $props = array()): string {
        if (isset($props["name"]) && !isset($props["id"])) {
            $props["id"] = CuxSlug::slugify($props["name"], "_", false);
        }
        return "<" . $tagName . static::renderAttributes($props) . ">";
    }

    /**
     * Closes an HTML tag
     * @param string $tagName The HTML tag to be closed
     * @return string
     */
    public static function endTag(string $tagName): string {
        return "</{$tagName}>";
    }

    /**
     * Creates a "style" HTML tag
     * @param string $content The content enclosed within the "style" tag
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function style(string $content, array $props = array()): string {
        return static::tag("style", $content, $props);
    }

    /**
     * Creates a "script" HTML tag
     * @param string $content The content enclosed within the "script" tag
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function script(string $content, array $props = array()): string {
        return static::tag("script", $content, $props);
    }

    /**
     * Adds a "link" HTML tag (CSS script file)
     * Special properties ("noscript" and "condition") can be added to load the file in special cases
     * @param string $url The URL path of the CSS script file
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function cssFile(string $url, array $props = array()): string {
        if (!isset($props["rel"])) {
            $props["rel"] = "stylesheet";
        }
        $props["href"] = $url;

        if (isset($props["noscript"]) && $props["noscript"] == true) {
            $props['noscript'] = null;
            unset($props['noscript']);
            return "<noscript>" . static::tag("link", "", $props) . "</noscript>";
        } elseif (isset($props["condition"])) {
            $condition = $props["condition"];
            $props["condition"] = null;
            unset($props["condition"]);
            return "<!--[if $condition]>" . static::tag("link", "", $props) . "<![endif]-->";
        } else {
            return static::tag("link", "", $props);
        }
    }

    /**
     * Creates a "script" HTML tag (JS script file)
     * Special properties ("noscript" and "condition") can be added to load the file in special cases
     * @param string $url The URL path of the JS script file
     * @param array The list of HTML attributes for the generated tag
     * @return string
     */
    public static function jsFile(string $url, array $props = array()): string {
        if (!isset($props["type"])) {
            $props["type"] = "text/javascript";
        }
        $props["src"] = $url;

        if (isset($props["noscript"]) && $props["noscript"] == true) {
            $props['noscript'] = null;
            unset($props['noscript']);
            return "<noscript>" . static::tag("script", "", $props) . "</noscript>";
        } elseif (isset($props["condition"])) {
            $condition = $props["condition"];
            $props["condition"] = null;
            unset($props["condition"]);
            return "<!--[if $condition]>" . static::tag("script", "", $props) . "<![endif]-->";
        } else {
            return static::tag("script", "", $props);
        }
    }

    /**
     * Starts an HTML form
     * @param string $action The form endpoint (i.e. send the form data to this endpoint)
     * @param string $method The HTTP request method (i.e. HTTP request VERB - POST, GET, PUT, PATCH, DELETE, HEAD, OPTIONS)
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function beginForm(string $action = "", string $method = "POST", array $props = array()): string {
        $props["method"] = $method;
        $props["action"] = $action;
        return static::beginTag("form", $props);
    }

    /**
     * Ends an HTML form
     * @return string
     */
    public static function endForm(): string {
        return static::endTag("form");
    }

    /**
     * Creates an "img" HTML tag (HTML images)
     * @param string $src The URL path for the image
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function img(string $src, array $props = array()): string {
        if (!isset($props["alt"])) {
            $props["alt"] = "";
        }
        $props["src"] = $src;
        return static::tag("img", "", $props);
    }

    /**
     * Creates an "a" HTML tag (HTML links)
     * @param string $content The content of the link
     * @param string $url The URL of the link
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function a(string $content, string $url = "", array $props = array()): string {
        $props["href"] = $url;
        return static::tag("a", $content, $props);
    }

    /**
     * Creates an "a" HTML tag (HTML send-mail links)
     * @param string $content The content of the link
     * @param string $email The email address of the recipient
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function mailto(string $content, string $email = "", array $props = array()): string {
        return static::a($content, "mailto:" . $email, $props);
    }

    /**
     * Creates a "label" HTML tag
     * @param string $content The content of the label
     * @param string $for The ID of the element for which the label is generated
     * @param string $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function label(string $content, string $for = "", $props = array()): string {
        if ($for) {
            $props["for"] = $for;
        }
        return static::tag("label", $content, $props);
    }

    /**
     * Creates an "input" HTML tag
     * @param string $type The HTML type of the input (i.e. text, checkbox, radio, file, hidden, etc.)
     * @param string $name The HTML name of the input
     * @param type $value The value of the element
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function input(string $type, string $name = "", $value = "", array $props = array()): string {
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

    /**
     * Creates an "input" HTML tag (HTML button)
     * @param string $name The HTML name of the input
     * @param type $value The value of the element
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function buttonInput(string $name = "", $value = "", array $props = array()): string {
        return static::input("button", $name, $value, $props);
    }

    /**
     * Creates a "input" HTML tag ( HTML submit button)
     * @param string $name The HTML name of the input
     * @param type $value The value of the element
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function submitInput(string $name = "", $value = "", array $props = array()): string {
        return static::input("submit", $name, $value, $props);
    }

    /**
     * Creates an "input" HTML tag ( HTML reset button)
     * @param string $name The HTML name of the input
     * @param type $value The value of the element
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function resetInput(string $name = "", $value = "", array $props = array()): string {
        return static::input("reset", $name, $value, $props);
    }

    /**
     * Creates an "input" HTML tag ( HTML text input)
     * @param string $name The HTML name of the input
     * @param string $value The value of the element
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function textInput(string $name, string $value = "", array $props = array()): string {
        return static::input("text", $name, $value, $props);
    }

    /**
     * Creates an "input" HTML tag ( HTML password input)
     * @param string $name The HTML name of the input
     * @param string $value The value of the element
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function passwordInput(string $name, string $value = "", array $props = array()): string {
        return static::input("password", $name, $value, $props);
    }

    /**
     * Creates an "input" HTML tag ( HTML hidden input)
     * @param string $name The HTML name of the input
     * @param string $value The value of the element
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function hiddentInput(string $name, string $value = "", array $props = array()): string {
        return static::input("hidden", $name, $value, $props);
    }

    /**
     * Creates an "input" HTML tag ( HTML file input)
     * @param string $name The HTML name of the input
     * @param string $value The value of the element
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function fileInput(string $name, string $value = "", array $props = array()): string {
        return static::input("file", $name, $value, $props);
    }

    /**
     * Creates a "button" HTML tag (HTML button)
     * @param string $content The label of the button
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function button(string $content = "", array $props = array()): string {
        if (!isset($props["type"])) {
            $props["type"] = "button";
        }
        return static::tag("button", $content, $props);
    }

    /**
     * Creates a "button" HTML tag (HTML submit button)
     * @param string $content The label of the button
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function submitButton(string $content = "Submit", array $props = array()): string {
        $props["type"] = "submit";
        return static::button($content, $props);
    }

    /**
     * Creates a "button" HTML tag (HTML reset button)
     * @param string $content The label of the button
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function resetButton(string $content = "Reset", array $props = array()): string {
        $props["type"] = "reset";
        return static::button($content, $props);
    }

    /**
     * Creates a "textarea" HTML tag (HTML text input)
     * @param string $name The HTML name of the input
     * @param type $value The current value of the element
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function textarea(string $name, $value, $props = array()): string {
        $props["name"] = $name;
        return static::tag("textarea", static::encode($value), $props);
    }

    /**
     * Creates an "input" HTML tag (HTML radio input)
     * Special property "pattern" can be added to change the generated output ( ie: "pattern" = array("label", "anythingiwant", "input") will generate HTML code like <label ...>bla</label>anythingiwant<input type="radio"... />)
     * Special property "label" can be added to prefix/suffix the radiobox with a given HTML label tag
     * Special property "labelProps" can be added to enrich the prefix/suffix HTML tabel tag. This should be also an indexed (key-valued) array
     * @param string $name The HTML name of the radiobox list
     * @param type $value The current value of the element
     * @param bool $checked Tells if the current checkbox is checked
     * @param type $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function radio(string $name, $value, bool $checked = false, $props = array()): string {
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

        if (isset($props["label"])) {
            $label = $props["label"];
            $props["label"] = null;
            unset($props["label"]);
        } else {
            $label = "";
        }

        if (isset($props["labelProps"])) {
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
                    if ($label) {
                        $ret .= static::label($label, $props["id"], $labelProps);
                    }
                    break;
                default:
                    $ret .= $pattern;
            }
        }
        return $ret;
    }

    /**
     * Creates an "input" HTML tag (HTML checkbox input)
     * Special property "pattern" can be added to change the generated output ( ie: "pattern" = array("label", "anythingiwant", "input") will generate HTML code like <label ...>bla</label>anythingiwant<input type="radio"... />)
     * Special property "label" can be added to prefix/suffix the radiobox with a given HTML label tag
     * Special property "labelProps" can be added to enrich the prefix/suffix HTML tabel tag. This should be also an indexed (key-valued) array
     * @param string $name The HTML name of the radiobox list
     * @param type $value The current value of the element
     * @param bool $checked Tells if the current checkbox is checked
     * @param type $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function checkbox(string $name, $value, bool $checked = false, $props = array()): string {
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

        if (isset($props["label"])) {
            $label = $props["label"];
            $props["label"] = null;
            unset($props["label"]);
        } else {
            $label = "";
        }

        if (isset($props["labelProps"])) {
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
                    if ($label) {
                        $ret .= static::label($label, $props["id"], $labelProps);
                    }
                    break;
                default:
                    $ret .= $pattern;
            }
        }
        return $ret;
    }

    /**
     * Creates an "option" HTML tag (HTML dropdown option)
     * @param array $item Key-valued array with at least "key" and "value" as properties
     * @param bool $selected If the current value is selected
     * @param type $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function dropdownOption(array $item, bool $selected = false, $props = array()): string {
        if ($selected) {
            $props["selected"] = "selected";
        }
        $props["value"] = $item["key"];

        return static::tag("option", $item["value"], $props);
    }

    /**
     * Creates a list of radio boxes
     * @param string $name The HTML name of the radiobox list
     * @param type $value The current selected value
     * @param array $elements Key-valued array representing the list of elements
     * @param type $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function radioboxList(string $name, $value, array $elements, $props = array()): string {
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

    /**
     * Creates a list of checkboxes
     * @param string $name The HTML name of the checkbox list
     * @param type $value The current selected value
     * @param array $elements Key-valued array representing the list of elements
     * @param type $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function checkboxList(string $name, $value, array $elements, $props = array()): string {
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
        if (substr($checkboxName, -1) != "]") {
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

    /**
     * Creates an "optgroup" HTML tag (HTML dropdown list group)
     * @param string $name The HTML name of the dropdown group
     * @param array $elements Key-valued array representing the list of elements
     * @param type $value The current selected value
     * @param type $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function dropdownOptionGroup(string $name, array $elements, $value, $props = array()): string {
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

    /**
     * Creates an HTML dropdown list
     * @param string $name The HTML name of the dropdown list
     * @param type $value The current selected value
     * @param array $elements Key-valued array representing the list of elements
     * @param type $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function dropdownList(string $name, $value, array $elements, $props = array()): string {
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

    /**
     * Creates a "label" HTML tag, based on a CuxObject model property
     * @param CuxObject $model The base model for which we generate content
     * @param string $attribute The attribute of the base model
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function activeLabel(CuxObject $model, string $attribute, array $props = array()): string {

        if (isset($props["label"])) {
            $label = $props["label"];
            $props["label"] = null;
            unset($props["label"]);
        } else {
            $label = $model->getAttributeLabel($attribute);
        }

        if (isset($props["for"])) {
            $for = $props["for"];
            $props["for"] = null;
            unset($props["for"]);
        } else {
            $for = $model->getAttributeId($attribute);
        }

        return static::label($label, $for, $props);
    }

    /**
     * Creates a "input" HTML tag (HTML hidden input), based on a CuxObject model property
     * @param CuxObject $model The base model for which we generate content
     * @param string $attribute The attribute of the base model
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function activeHiddenInput(CuxObject $model, string $attribute, array $props = array()): string {
        return static::hiddentInput($model->getAttributeName($attribute), $model->getAttribute($attribute), $props);
    }

    /**
     * Creates a "input" HTML tag (HTML text input), based on a CuxObject model property
     * @param CuxObject $model The base model for which we generate content
     * @param string $attribute The attribute of the base model
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function activeTextInput(CuxObject $model, string $attribute, array $props = array()): string {
        if (!isset($props["id"])) {
            $props["id"] = $model->getAttributeId($attribute);
        }
        return static::textInput($model->getAttributeName($attribute), $model->getAttribute($attribute), $props);
    }

    /**
     * Creates a "textarea" HTML tag (HTML text input), based on a CuxObject model property
     * @param CuxObject $model The base model for which we generate content
     * @param string $attribute The attribute of the base model
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function activeTextarea(CuxObject $model, string $attribute, array $props = array()): string {
        if (!isset($props["id"])) {
            $props["id"] = $model->getAttribute($attribute);
        }
        return static::textarea($model->getAttributeName($attribute), $model->getAttribute($attribute), $props);
    }

    /**
     * Creates a "input" HTML tag (HTML checkbox input), based on a CuxObject model property
     * @param CuxObject $model The base model for which we generate content
     * @param string $attribute The attribute of the base model
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function activeCheckbox(CuxObject $model, string $attribute, array $props = array()): string {
        $value = isset($props["value"]) ? $props["value"] : 1;
        $checked = $model->getAttribute($attribute) != false;
        $attrName = $model->getAttributeName($attribute);
        $attrId = CuxSlug::slugify($attrName);
        $ret = static::checkbox($attrName, $value, $checked, $props);
        if (!isset($props["no_default"])) {
            $ret = static::hiddentInput($attrName, 0, array("id" => $attrId . "_default")) . "" . $ret;
        }
        return $ret;
    }

    /**
     * Creates a list of HTML checkboxes, based on a CuxObject model property
     * @param CuxObject $model The base model for which we generate content
     * @param string $attribute The attribute of the base model
     * @param array $elements Key-valued array representing the list of elements for the checkbox list
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function activeCheckboxlist(CuxObject $model, string $attribute, array $elements = array(), array $props = array()): string {
        if (!isset($props["id"])) {
            $props["id"] = $model->getAttributeId($attribute);
        }
        return static::checkboxList($model->getAttributeName($attribute), $model->getAttribute($attribute), $elements, $props);
    }

    /**
     * Creates a list of HTML radioboxes, based on a CuxObject model property
     * @param CuxObject $model The base model for which we generate content
     * @param string $attribute The attribute of the base model
     * @param array $elements Key-valued array representing the list of elements for the radiobox list
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function activeRadioboxList(CuxObject $model, string $attribute, array $elements = array(), array $props = array()): string {
        if (!isset($props["id"])) {
            $props["id"] = $model->getAttributeId($attribute);
        }
        return static::radioboxList($model->getAttributeName($attribute), $model->getAttribute($attribute), $elements, $props);
    }

    /**
     * Creates a HTML dropdownlist, based on a CuxObject model property
     * @param CuxObject $model
     * @param string $attribute
     * @param array $elements
     * @param array $props The list of HTML attributes for the generated tag
     * @return string
     */
    public static function activeDropdownList(CuxObject $model, string $attribute, array $elements = array(), array $props = array()): string {
        if (!isset($props["id"])) {
            $props["id"] = $model->getAttributeId($attribute);
        }
        return static::dropdownList($model->getAttributeName($attribute), $model->getAttribute($attribute), $elements, $props);
    }

    /**
     * Returns a key-value indexed array based of given properties from a list of CuxObject models
     * @param CuxObject[] $list
     * @param type $key
     * @param type $val
     * @return array
     */
    public static function modelsToArray(array $list, $key, $val): array {
        $ret = array();
        foreach ($list as $item) {
            $ret[$item->getAttribute($key)] = $item->getAttribute($val);
        }
        return $ret;
    }

}
