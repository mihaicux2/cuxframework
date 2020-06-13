<?php
/**
 * CuxLayout class file
 * 
 * @package Components
 * @subpackage Layout
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\components\layout;

use CuxFramework\utils\CuxBaseObject;
use CuxFramework\utils\CuxBase;
use CuxFramework\utils\Cux;
use Composer;

/**
 * Base class that can be used to handle content rendering
 */
class CuxLayout extends CuxBaseObject {

    /**
     * The current Module
     * @var string
     */
    private $_moduleName;
    
    /**
     * The current Controller
     * @var string
     */
    private $_controllerName;
    
    /**
     * The location of existing view files
     * @var string 
     */
    public $viewsFolder = "views";
    
    /**
     * Default layout file
     * @var string
     */
    public $layoutName = "main";
    
    /**
     * The location of existing layout files
     * @var string
     */
    public $layoutsFolder = "layouts";
    
    /**
     * The extension for the existing view files
     * @var string
     */
    public $viewExtension = ".php";
    
    /**
     * To be used for the HTML page title
     * @var string
     */
    public $pageTitle = "";

    /**
     * Setup object instance properties
     * @param array $config
     */
    public function config(array $config) {
        parent::config($config);
    }

    /**
     * Setter for the $_moduleName property
     * @param string $moduleName
     * @return \CuxFramework\components\layout\CuxLayout
     */
    public function setModuleName(string $moduleName): CuxLayout {
        $this->_moduleName = $moduleName;
        return $this;
    }

    /**
     * Setter for the $_controllerName property
     * @param string $controllerName
     * @return \CuxFramework\components\layout\CuxLayout
     */
    public function setControllerName(string $controllerName): CuxLayout {
        $this->_controllerName = $controllerName;
        return $this;
    }

    /**
     * Setter for the $pageTitle property
     * @param string $title
     * @return \CuxFramework\components\layout\CuxLayout
     */
    public function setPageTitle(string $title): CuxLayout {
        $appName = Cux::getInstance()->appName;
        if ($appName) {
            $this->pageTitle = $title . " | " . $appName;
        } else {
            $this->pageTitle = $title;
        }
        return $this;
    }

    /**
     * Setter for the $layoutName property
     * @param string $layoutName
     * @return \CuxFramework\components\layout\CuxLayout
     */
    public function setLayout(string $layoutName): CuxLayout {
        $this->layoutName = $layoutName;
        return $this;
    }

    /**
     * Generates the "absolute" path for a given view ( not necessary a valid path )
     * ie: "views/$viewName.$viewExtension"
     * @param string $viewName The name/path of the view to be rendered
     * @return string
     */
    private function getAbsoluteViewPath(string $viewName): string {
        return $this->viewsFolder . DIRECTORY_SEPARATOR . $viewName . $this->viewExtension;
    }

    /**
     * Generates the "relative" path for a given view, MVC-wise ( not necessary a valid path )
     * ie: "$module/views/$controller/$viewName.$viewExtension"
     * @param string $viewName The name/path of the view to be rendered
     * @return string
     */
    private function getModuleRelativeViewPath(string $viewName): string { // return modules/$module/$views/$controller/$viewName.$viewExtension
        $path = "";
        if ($this->_moduleName) {
            $path .= "modules" . DIRECTORY_SEPARATOR . $this->_moduleName . DIRECTORY_SEPARATOR;
        }
        $path .= $this->viewsFolder . DIRECTORY_SEPARATOR;
        if ($this->_controllerName) {
            $path .= $this->_controllerName . DIRECTORY_SEPARATOR;
        }
        $path .= $viewName . $this->viewExtension;

        return $path;
    }

    /**
     * Generates the "relative" path for a given view, MVC-wise ( not necessary a valid path )
     * ie: "views/$module/$controller/$viewName.$viewExtension"
     * @param string $viewName The name/path of the view to be rendered
     * @return string
     */
    private function getAppRelativeViewPath(string $viewName): string { // return views/$module/$controller/$viewName.$viewExtension
        $path = $this->viewsFolder . DIRECTORY_SEPARATOR;
        if ($this->_moduleName) {
            $path .= $this->_moduleName . DIRECTORY_SEPARATOR;
        }
        if ($this->_controllerName) {
            $path .= $this->_controllerName . DIRECTORY_SEPARATOR;
        }
        $path .= $viewName . $this->viewExtension;

        return $path;
    }

    /**
     * Tries to find the real path of a view, based on the current module and controller, following the next steps:
     *     1. Check if the viewName is absolute (if the $viewName starts with "//"):
     *             a. Check for files like views/$viewName.$viewExtension
     *             b. Check for files like $frameworkPath/views/$viewName.$viewExtension
     *              OR
     *     2. Check for MVC based views:
     *             a. Check for "modules/$module/views/$controller/$viewName.$viewExtension"
     *             b. Check for "views/$module/$controller/$viewName.$viewExtension"
     *             c. Check for "$frameworkPath/views/$module/$controller/$viewName.$viewExtension"
     *             d. Check for "$frameworkPath/views/$module/$controller/$viewName.$viewExtension"
     * @param string $viewName The name/path of the view to be rendered
     * @return string The first path for the given $viewName
     */
    private function getViewRealPath(string $viewName): string {

        $realPath = "";

        $frameworkPath = Cux::getFrameworkPath();
        if (strpos($viewName, "//") !== false) { // check for "views/$viewName.$viewExtension"
            $absoluteViewPath = $this->getAbsoluteViewPath(substr($viewName, 2));
            $possiblePaths[] = $absoluteViewPath;
            $possiblePaths[] = $frameworkPath . $absoluteViewPath;
        } else {
            $moduleRelativePath = $this->getModuleRelativeViewPath($viewName); // check for "modules/$module/views/$controller/$viewName.$viewExtension"
            $appRelativePath = $this->getAppRelativeViewPath($viewName); // check for "views/$module/$controller/$viewName.$viewExtension"
            $possiblePaths[] = $moduleRelativePath;
            $possiblePaths[] = $appRelativePath;
            $possiblePaths[] = $frameworkPath . $moduleRelativePath;  // search inside the framework for "modules/$module/views/$controller/$viewName.$viewExtens
            $possiblePaths[] = $frameworkPath . $appRelativePath; // search inside the framework for "views/$module/$controller/$viewName.$viewExtension"
        }

        foreach ($possiblePaths as $path) {
            if (file_exists($path) && is_readable($path)) {
                $realPath = $path;
                break;
            }
        }

        return $realPath;
    }

    /**
     * Full render for a given view ( based on the current $layoutName ), using the provided arguments
     * @param string $viewName The name of the view to be rendered
     * @param array $params The list of parameters to be used by/sent to the view file
     * @param bool $includeScripts Flag to tell the renderer to process the output or not (i.e. include CSS and JS scripts and files )
     * @return string
     * @throws \Exception
     */
    public function render(string $viewName, array $params = array(), bool $includeScripts = true): string {
        $layoutPath = $this->viewsFolder . DIRECTORY_SEPARATOR . $this->layoutsFolder . DIRECTORY_SEPARATOR . $this->layoutName . $this->viewExtension;
        $content = $this->renderPartial($viewName, $params, false);
        $pageTitle = $this->pageTitle;
        ob_start();
        ob_implicit_flush(false);
        if (file_exists($layoutPath) && is_readable($layoutPath)) {
            if (!@include($layoutPath)) {
                throw new \Exception(Cux::translate("error", "Layout not found: {layout}", array(
                            "{layout}" => $this->layoutName
                        )), 501);
            }
        } else {
            $layoutPath = "vendor/mihaicux/cuxframework/src/CuxFramework/" . $layoutPath;
            if (!@include($layoutPath)) {
                throw new \Exception(Cux::translate("error", "Layout not found: {layout}", array(
                            "{layout}" => $this->layoutName
                        )), 501);
            }
        }
        $output = ob_get_clean();
        if ($includeScripts) {
            $output = Cux::getInstance()->clientScript->processOutput($output);
        }
        return $output;
    }

    /**
     * Render a given view, using the provided arguments
     * @param string $viewName The name of the view to be rendered
     * @param array $params The list of parameters to be used by/sent to the view file
     * @param bool $includeScripts Flag to tell the renderer to process the output or not (i.e. include CSS and JS scripts and files )
     * @return string
     * @throws \Exception
     */
    public function renderPartial(string $viewName, array $params = array(), bool $includeScripts = true): string {

        $realPath = $this->getViewRealPath($viewName);
        if ($realPath) {
            if (!empty($params)) {
                extract($params);
            }
            ob_start();
            ob_implicit_flush(false);
            @include($realPath);
            $output = ob_get_clean();
            if ($includeScripts) {
                $output = Cux::getInstance()->clientScript->processOutput($output);
            }
            return $output;
        }

        throw new \Exception(Cux::translate("error", "View not found: {view}", array(
                    "{view}" => $viewName
                )), 501);
    }

}
