<?php

namespace CuxFramework\modules\cuxDefault\controllers;

use CuxFramework\utils\Cux;
use CuxFramework\components\user\CuxUser;
use CuxFramework\utils\CuxBaseObject;

class CuxDefaultController extends CuxBaseObject {

    public $defaultAction = "index";
    protected $_action;
    protected $_actionName;
    protected $_params = array();
    public $pageTitle = "";

    public function config(array $config) {
        parent::config($config);
    }

    public function setPageTitle(string $title) {
        $this->pageTitle = $title;
        Cux::getInstance()->layout->setPageTitle($title);
    }

    public function setLayout(string $layout) {
        Cux::getInstance()->layout->setLayout($layout);
    }

    public function beforeAction(string $actionName): bool {
        return true;
    }

    public function afterAction(string $actionName) {
        
    }

    public function getName(): string {
        return lcfirst(substr((new \ReflectionClass($this))->getShortName(), 0, -10));
    }
    
    public function getParams(string $actionName): array {
        try {
            $route = Cux::getInstance()->urlManager->getMatchedRoute();
            $routeInfo = $route->getDetails();
            return $routeInfo["params"];
        } catch (\Exception $e){
            throw new \Exception(Cux::translate("core.errors", "Invalid action", array(), "Message shown on PageNotFound exception"), 404);
        }
    }

    private function getStringWithoutParams(string $actionName): string {
        $ret = $actionName;
        if (($pos = strpos($actionName, "?")) != false) {
            $ret = substr($actionName, 0, $pos);
        }
        return $ret;
    }

    private function getFullyQualifiedActionName(string $actionName): string {
        $ret = "action" . ucfirst($this->getStringWithoutParams($actionName));
        return $ret;
    }

    public function loadAction(string $actionName) {
        $this->_actionName = $actionName;
        $this->_params = $this->getParams($actionName);

        $action = $this->getFullyQualifiedActionName($actionName);
        if (!method_exists($this, $action)) {
            throw new \Exception(Cux::translate("core.errors", "Invalid action", array(), "Message shown on PageNotFound exception"), 404);
        }
        
        $this->_action = $action;
        if (!$this->pageTitle) {
            $this->pageTitle = ucfirst($actionName);
        }

        Cux::getInstance()->layout->setPageTitle($this->pageTitle);
    }

    public function getAction(): string {
        return $this->_action;
    }

    public function run() {
        if ($this->beforeAction($this->_actionName)) {
            call_user_func(array($this, $this->_action), $this->_params);
            $this->afterAction($this->_actionName);
        }
    }

    public function actionIndex($params) {
        echo  $this->render("index");
    }

    public function render(string $view, array $params = array(), bool $includeScripts = true): string{
        return Cux::getInstance()->layout->render($view, $params, $includeScripts);
    }

    public function renderPartial(string $view, array $params = array(), bool $includeScripts = true): string{
        return Cux::getInstance()->layout->renderPartial($view, $params, $includeScripts);
    }

    protected function _output($filename) {
        $filesize = filesize($filename);

        $chunksize = 4096;
        if ($filesize > $chunksize) {
            $srcStream = fopen($filename, 'rb');
            $dstStream = fopen('php://output', 'wb');

            $offset = 0;
            while (!feof($srcStream)) {
                $offset += stream_copy_to_stream($srcStream, $dstStream, $chunksize, $offset);
            }

            fclose($dstStream);
            fclose($srcStream);
        } else {
            echo file_get_contents($filename);
        }
    }

}
