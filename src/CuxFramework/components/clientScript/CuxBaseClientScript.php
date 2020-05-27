<?php

namespace CuxFramework\components\clientScript;

use CuxFramework\utils\CuxBaseObject;
use CuxFramework\utils\Cux;
use CuxFramework\components\html\CuxHTML;

/**
 * Simple class used to manage all the JS and CSS scripts
 */
abstract class CuxBaseClientScript extends CuxBaseObject {
    
    const POSITION_HEAD = 0; // add scripts in the <head> tag
    const POSITION_BEGIN = 1; // add scripts at the top of the <body> tag
    const POSITION_END = 2; // add scripts at the bottom of the <body> tag
    const POSITION_READY = 3; // add JS at the "document.ready" event
    
    protected $_jsFiles = array();
    protected $_cssFiles = array();
    
    protected $_styles = array();
    protected $_scripts = array();
    
    protected static $_scriptPositions = null;
    
    public function config(array $config) {
        parent::config($config);
    }
    
    public static function getScriptPositions(): array {
        if (is_null(static::$_scriptPositions)) {
            static::$_scriptPositions = array(
                self::POSITION_HEAD => "head",
                self::POSITION_BEGIN => "head",
                self::POSITION_END => "head",
                self::POSITION_READY => "head",
            );
        }
        return static::$_scriptPositions;
    }
    
    protected function checkPosition(int $position): bool {
        $scriptPositions = static::getScriptPositions();
        return isset($scriptPositions[$position]);
    }
    
    abstract public function registerCSS(string $id, string $cssContent, int $position = self::POSITION_END, string $media = "all"): bool;
    
    abstract public function registerJS(string $id, string $jsContent, int $position = self::POSITION_END): bool;
    
    abstract public function registerCSSFile(string $filePath, int $position = self::POSITION_HEAD, array $props = array()): bool;
    
    abstract public function registerJSFile(string $filePath, int $position = self::POSITION_HEAD, array $props = array()): bool;
    
    protected function renderPart(int $position): string{
        if (!$this->checkPosition($position)) return "";
        
        $out = "";
        
        if ($position != self::POSITION_READY){
            // CSS files
            if (isset($this->_cssFiles[$position]) && !empty($this->_cssFiles[$position])){
                $out .= implode("\n", $this->_cssFiles[$position]);
            }

            // CSS inline styles
            if (isset($this->_styles[$position]) && !empty($this->_styles[$position])){
                foreach ($this->_styles[$position] as $styleMedia){
                    foreach ($styleMedia as $media => $styles){
                        $out .= CuxHTML::beginTag("style", array("media"=>$media));
                        $out .= implode("\n", $styles);
                        $out .= CuxHTML::endTag("style");
                    }
                }
            }

            // JS files
            if (isset($this->_jsFiles[$position]) && !empty($this->_jsFiles[$position])){
                $out .= implode("\n", $this->_jsFiles[$position]);
            }
        }
        
        // JS inline scripts
        if (isset($this->_scripts[$position]) && !empty($this->_scripts[$position])){
            foreach ($this->_scripts[$position] as $scripts){
                $out .= CuxHTML::beginTag("script", array("type"=>"text/javascript"));
                if ($position == self::POSITION_READY){
                    $out .= "\jQuery(document).ready(function(){\n";
                }
                $out .= implode("\n", $scripts);
                if ($position == self::POSITION_READY){
                    "\n});";
                }
                $out .= CuxHTML::endTag("script");
            }
        }
        
        return $out;
        
    }
    
    abstract public function renderHead(): string;
    
    abstract public function renderBegin(): string;
    
    abstract public function renderEnd(): string;
    
    abstract public function renderReady(): string;
    
    abstract public function processOutput(string $htmlOutput): string;
    
}