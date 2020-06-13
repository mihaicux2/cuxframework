<?php

/**
 * CuxClientScript class file
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
 * Simple class used to manage all the JS and CSS scripts
 */
class CuxClientScript extends CuxBaseClientScript {

    /**
     * Setup object instance properties
     * @param array $config
     */
    public function config(array $config) {
        parent::config($config);        
    }

    /**
     * Render the HTML "link" tag, using the provided arguments
     * @param string $filePath The URL of the actual CSS file
     * @param array $props The list of HTML properties for the ( to be- ) generated "link" tag
     * @return string
     */
    private function renderCSSFile(string $filePath, array $props = array()): string {
        $props["rel"] = "stylesheet";
        $props["href"] = $filePath;
        return CuxHTML::tag("link", "", $props);
    }
    
    /**
     * Render the HTML "script" tag, using the provided arguments
     * @param string $filePath The URL of the actual JS file
     * @param array $props The list of HTML properties for the ( to be- ) generated "script" tag
     * @return string
     */
    private function renderJSFile(string $filePath, array $props = array()): string {
        $props["type"] = "text/javascript";
        $props["src"] = $filePath;
        return CuxHTML::tag("script", "", $props);
    }
    
    /**
     * Store a certain CSS style to be rendered at a given position in the resulting HTML page
     * @param string $id The CSS content id
     * @param string $cssContent The CSS itself
     * @param int $position Where to render the CSS content
     * @param $media Apply the CSS content to certain media types (i.e. "all", "print", etc.)
     * @return bool True if registration is successfull
     */
    public function registerCSS(string $id, string $cssContent, int $position = self::POSITION_END, string $media = "all"): bool {
        
        if (!$this->checkPosition($position))
            return false;
        
        if (!isset($this->_styles[$position])) {
            $this->_styles[$position] = array();
        }
        
        if (!isset($this->_styles[$position])) {
            $this->_styles[$poisition][$media] = array();
        }

        $this->_styles[$poisition][$media][$id] = $cssContent;

        return true;
    }

    /**
     * Store a certain JS script to be rendered at a given position in the resulting HTML page
     * @param string $id The JS content id
     * @param string $jsContent The javaScript itself
     * @param int $position Where to render the JS content
     * @return bool True if registration is successfull
     */
    public function registerJS(string $id, string $jsContent, int $position = self::POSITION_END): bool {

        if (!$this->checkPosition($position))
            return false;

        if (!isset($this->_scripts[$position])) {
            $this->_scripts[$position] = array();
        }

        $this->_scripts[$position][$id] = $jsContent;

        return true;
    }

    /**
     * Register a new CSS file that will be included in the generated HTML layout.
     * @param string $filePath The path for the CSS file to be included
     * @param int $position Where in the generated HTML to include the CSS file
     * @param array $props "link" HTML tag properties. Here you can also mention the "media" property
     * @return bool True if the script registered successfully
     */
    public function registerCSSFile(string $filePath, int $position = self::POSITION_HEAD, array $props = array()): bool {

        if (!$this->checkPosition($position))
            return false;

        if (!isset($this->_cssFiles[$position])) {
            $this->_cssFiles[$position] = array();
        }

        $this->_cssFiles[$position][] = $this->renderCSSFile($filePath, $props);

        return true;
    }

    /**
     * Register a new JS file that will be included in the generated HTML layout.
     * @param string $filePath The path for the JS file to be included
     * @param int $position Where in the generated HTML to include the JS file
     * @param array $props "link" HTML tag properties. Here you can also mention the "media" property
     * @return bool True if the script registered successfully
     */
    public function registerJSFile(string $filePath, int $position = self::POSITION_HEAD, array $props = array()): bool {

        if (!$this->checkPosition($position))
            return false;

        
        if (!isset($this->_jsFiles[$position])) {
            $this->_jsFiles[$position] = array();
        }

        $this->_jsFiles[$position][] = $this->renderJSFile($filePath, $props);

        return true;
    }
    
    /**
     * Render all the scripts (CSS and JS) assigned for the HTML  head DOM part ( <head>list of scripts</head> )
     * @return string
     */
    public function renderHead(): string{
        return $this->renderPart(self::POSITION_HEAD);
    }
    
    /**
     * Render all the scripts (CSS and JS) assigned for the HTML body DOM part ( <body>list of scripts, OTHER HTML elements</body> )
     * @return string
     */
    public function renderBegin(): string{
        return $this->renderPart(self::POSITION_BEGIN);
    }
    
    /**
     * Render all the scripts (CSS and JS) assigned at the end of the HTML body DOM part ( <body>OTHER HTML elements, list of scripts</body> )
     * @return string
     */
    public function renderEnd(): string{
        return $this->renderPart(self::POSITION_END);
    }
    
    /**
     * Renders all the scripts (JS only) assigned at the DOM ready event ( <script>jQuery(document).ready(function(){ .... JS scripts })</script> )
     * @return string
     */
    public function renderReady(): string{
        return $this->renderPart(self::POSITION_READY);
    }
    
    /**
     * Process assigned scripts and append them to the existing HTML document
     * @param string $htmlOutput The unprocessed HTML
     * @return string
     */
    public function processOutput(string $htmlOutput): string{
        $htmlOutput = str_ireplace("<head>", "<head>\n".$this->renderHead(), $htmlOutput);
        $htmlOutput = str_ireplace("<body>", "<body>\n".$this->renderBegin(), $htmlOutput);
        $htmlOutput = str_ireplace("</body>", $this->renderEnd()."\n</body>", $htmlOutput);
        $htmlOutput = str_ireplace("</body>", $this->renderReady()."\n</body>", $htmlOutput);
        return $htmlOutput;
    }
    
}
