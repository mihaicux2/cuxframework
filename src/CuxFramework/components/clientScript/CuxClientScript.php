<?php

/**
 * CuxClientScript class file
 */

namespace CuxFramework\components\clientScript;

use CuxFramework\utils\CuxBaseObject;
use CuxFramework\utils\Cux;
use CuxFramework\components\html\CuxHTML;

/**
 * Simple class used to manage all the JS and CSS scripts
 */
class CuxClientScript extends CuxBaseClientScript {

    public function config(array $config) {
        parent::config($config);        
    }

    private function renderCSSFile(string $filePath, int $position = self::POSITION_HEAD, array $props = array()): string {
        $props["rel"] = "stylesheet";
        $props["href"] = $filePath;
        return CuxHTML::tag("link", "", $props);
    }
    
    private function renderJSFile(string $filePath, int $position = self::POSITION_HEAD, array $props = array()): string {
        $props["type"] = "text/javascript";
        $props["src"] = $filePath;
        return CuxHTML::tag("script", "", $props);
    }
    
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

        $this->_cssFiles[$position][] = $this->renderCSSFile($filePath, $position, $props);

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

        $this->_jsFiles[$position][] = $this->renderJSFile($filePath, $position, $props);

        return true;
    }
    
    public function renderHead(): string{
        return $this->renderPart(self::POSITION_HEAD);
    }
    
    public function renderBegin(): string{
        return $this->renderPart(self::POSITION_BEGIN);
    }
    
    public function renderEnd(): string{
        return $this->renderPart(self::POSITION_END);
    }
    
    public function renderReady(): string{
        return $this->renderPart(self::POSITION_READY);
    }
    
    public function processOutput(string $htmlOutput): string{
        $htmlOutput = str_ireplace("<head>", "<head>\n".$this->renderHead(), $htmlOutput);
        $htmlOutput = str_ireplace("<body>", "<body>\n".$this->renderBegin(), $htmlOutput);
        $htmlOutput = str_ireplace("</body>", $this->renderEnd()."\n</body>", $htmlOutput);
        $htmlOutput = str_ireplace("</body>", $this->renderReady()."\n</body>", $htmlOutput);
        return $htmlOutput;
    }
    
}
