<?php

namespace CuxFramework\controllers\cuxDefault;

use CuxFramework\utils\Cux;
use CuxFramework\components\user\CuxUser;
use CuxFramework\utils\CuxSingleton;

class CuxDefaultController extends CuxSingleton {

    public $defaultAction = "index";
    protected $_action;
    protected $_actionName;
    protected $_params = array();
    public $pageTitle = "";

    public static function config(array $config): void {
        parent::config($config);
    }

    public function setPageTitle(string $title): void {
        $this->pageTitle = $title;
        Cux::getInstance()->layout->setPageTitle($title);
    }

    public function setLayout(string $layout): void {
        Cux::getInstance()->layout->setLayout($layout);
    }

    public function beforeAction(string $actionName): bool {
        return true;
    }

    public function afterAction(string $actionName): void {
        
    }

    public function getName(): string {
        return lcfirst(substr((new \ReflectionClass($this))->getShortName(), 0, -10));
    }

    private function getParams(string $actionName): array {
        $ret = array();
        if (($pos = strpos($actionName, "?")) != false) {
            parse_str(substr($actionName, $pos + 1), $ret);
        }
        $ret = array_merge($ret, $_GET);

//        $uri = Cux::getInstance()->urlManager->getRoute(Cux::getInstance()->request);
//        die($uri);
//        $extraParams = substr($uri, strpos($uri, $actionName));

        return $ret;
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

    public function loadAction(string $actionName): void {
        $this->_actionName = $actionName;
        $this->_params = $this->getParams($actionName);

        $action = $this->getFullyQualifiedActionName($actionName);
        if (!method_exists($this, $action)) {
            throw new \Exception("Actiune invalida", 404);
        }
        $this->_action = $action;
        if (!$this->pageTitle) {
            $this->pageTitle = $actionName;
        }

        Cux::getInstance()->layout->setPageTitle($this->pageTitle);
    }

    public function getAction(): string {
        return $this->_action;
    }

    public function run(): void {
        if ($this->beforeAction($this->_actionName)) {
            call_user_func(array($this, $this->_action), $this->_params);
            $this->afterAction($this->_actionName);
        }
    }

    public function actionIndex($params) {
        $this->render("index");
    }

    protected function render($view, $params = array()) {
        echo Cux::getInstance()->layout->render($view, $params);
    }

    protected function renderPartial($view, $params = array()) {
        echo Cux::getInstance()->layout->renderPartial($view, $params);
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
