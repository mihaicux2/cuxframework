<?php

/**
 * CuxBaseClientScript abstract class file
 * 
 * @package Components
 * @subpackage ClientScript
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\components\clientScript;

use CuxFramework\utils\CuxBaseObject;
use CuxFramework\utils\Cux;
use CuxFramework\components\html\CuxHTML;

/**
 * Simple base class used to manage all the JS and CSS scripts
 */
abstract class CuxBaseClientScript extends CuxBaseObject {
    
    const POSITION_HEAD = 0; // add scripts in the <head> tag
    const POSITION_BEGIN = 1; // add scripts at the top of the <body> tag
    const POSITION_END = 2; // add scripts at the bottom of the <body> tag
    const POSITION_READY = 3; // add JS at the "document.ready" event
    
    /**
     * The list of JS files to be included in the resulting HTML page
     * @var array
     */
    protected $_jsFiles = array();
    
    /**
     * The list of CSS files to be included in the resulting HTML page
     * @var array
     */
    protected $_cssFiles = array();
    
    /**
     * The list of CSS (<style> tags ) styles to be included in the resulting HTML page
     * @var array
     */
    protected $_styles = array();
    
    /**
     * The list of JS (<script> tags ) scripts to be included in the resulting HTML page
     * @var array
     */
    protected $_scripts = array();
    
    /**
     * The list of available script loading positions (in the resulting HTML page)
     * @var array
     */
    protected static $_scriptPositions = null;
    
    /**
     * Setup class properties
     * @param array $config
     */
    public function config(array $config) {
        parent::config($config);
    }
    
    /**
     * Get labels for the defined available script positions
     * @return array
     */
    public static function getScriptPositions(): array {
        if (is_null(static::$_scriptPositions)) {
            static::$_scriptPositions = array(
                self::POSITION_HEAD => "head",
                self::POSITION_BEGIN => "begin",
                self::POSITION_END => "end",
                self::POSITION_READY => "ready",
            );
        }
        return static::$_scriptPositions;
    }
    
    /**
     * Check if a given script position is valid
     * @param int $position
     * @return bool
     */
    protected function checkPosition(int $position): bool {
        $scriptPositions = static::getScriptPositions();
        return isset($scriptPositions[$position]);
    }
    
    /**
     * Store a certain CSS style to be rendered at a given position in the resulting HTML page
     * @param string $id The CSS content id
     * @param string $cssContent The CSS itself
     * @param int $position Where to render the CSS content
     * @param $media Apply the CSS content to certain media types (i.e. "all", "print", etc.)
     * @return bool True if registration is successfull
     */
    abstract public function registerCSS(string $id, string $cssContent, int $position = self::POSITION_END, string $media = "all"): bool;
    
    /**
     * Store a certain JS script to be rendered at a given position in the resulting HTML page
     * @param string $id The JS content id
     * @param string $jsContent The javaScript itself
     * @param int $position Where to render the JS content
     * @return bool True if registration is successfull
     */
    abstract public function registerJS(string $id, string $jsContent, int $position = self::POSITION_END): bool;
    
    /**
     * Register a new CSS file that will be included in the generated HTML layout.
     * @param string $filePath The URL for the registered CSS file
     * @param int $position Where to load the CSS file
     * @param array $props A list of properties for the resulting HTML tag ( <style> )
     * @return bool True if registration is successfull
     */
    abstract public function registerCSSFile(string $filePath, int $position = self::POSITION_HEAD, array $props = array()): bool;
    
    /**
     * Register a new JS file that will be included in the generated HTML layout.
     * @param string $filePath The URL for the registered JS file
     * @param int $position Where to load the JS file
     * @param array $props A list of properties for the resulting HTML tag ( <script> )
     * @return bool True if registration is successfull
     */
    abstract public function registerJSFile(string $filePath, int $position = self::POSITION_HEAD, array $props = array()): bool;
    
    /**
     * Renders all the scripts (JS and CSS) for a certain HTML position
     * @param int $position Where to load the content
     * @return string The resulting HTML content
     */
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
    
    /**
     * Render all the scripts for the HEAD position
     * @return string
     */
    abstract public function renderHead(): string;
    
    /**
     * Render all the scripts for the BEGIN position
     * @return string
     */
    abstract public function renderBegin(): string;
    
    /**
     * Render all the scripts for the END position
     * @return string
     */
    abstract public function renderEnd(): string;
    
    /**
     * Render all the scripts for the READY position
     * @return string
     */
    abstract public function renderReady(): string;
    
    /**
     * Update a given HTML content, adding all the registered scripts in their corresponding positions
     * @params $htmlOutput The HTML to be enriched by the existing scripts
     * @return string The enriched HTML content
     */
    abstract public function processOutput(string $htmlOutput): string;
    
}