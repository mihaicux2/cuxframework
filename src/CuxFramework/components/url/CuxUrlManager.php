<?php

namespace CuxFramework\components\url;

use CuxFramework\utils\CuxBaseObject;
use CuxFramework\components\request\CuxRequest;
use CuxFramework\utils\Cux;

class CuxUrlManager extends CuxBaseObject {

    public $defaultAction = "";
    public $routes = array();

    public function config(array $config) {
        parent::config($config);

        if (!is_array($this->routes)) {
            $this->routes = array();
        }
    }

    public function getRequestParams(string $route, string $path): array {
        preg_match("|" . str_replace("/", "\\/", $route) . "|im", $path, $matches);
        return $matches;
    }

    public function getRoute(CuxRequest $request): string {
        if (!empty($this->routes)) {
            foreach ($this->routes as $route => $match) {
                $route = $this->processPattern($route);
                if (preg_match("|" . str_replace("/", "\\/", $route) . "$|im", $request->getPath(), $matches)) {
                    $params = $this->getRequestParams($route, $request->getPath());
                    if (($x = count($matches)) > 1) {
                        for ($i = 1; $i < $x; $i++) {
                            $match = str_replace("\$$i", $matches[$i], $match);
                        }
                    }
                    return $route;
                }
            }
        }
        return $this->parseRequest($request);
    }

    public function parseRequest(CuxRequest $request): string {
        if (!empty($this->routes)) {
            foreach ($this->routes as $route => $match) {
                $route = $this->processPattern($route);
                if (preg_match("|" . str_replace("/", "\\/", $route) . "$|im", $request->getPath(), $matches)) {
                    $params = $this->getRequestParams($route, $request->getPath());
                    if (($x = count($matches)) > 1) {
                        for ($i = 1; $i < $x; $i++) {
                            $match = str_replace("\$$i", $matches[$i], $match);
                        }
                    }
                    return $match;
                }
            }
        }
        $path = $request->getPath();

        if (!$path || $path == "/") {
            return $this->defaultAction;
        }
        if (substr($path, 0, 1) == "/") {
            $path = substr($path, 1);
        }
        $path = explode("/", $path);

        $paramsCount = count($path);
        switch ($paramsCount) {
            case 1:
                return $path[0];
                break;
            case 2:
                return $path[0] . "/" . $path[1];
                break;
            case 3:
            default:
//                if (!$path[2]){
//                    $path[2] = \modules\CuxDefaultModule::$defaultAction;
//                }
                return $path[0] . "/" . $path[1] . "/" . $path[2];
        }
    }

    public function getBasePath(): string {
        return realpath(__DIR__ . "/../../") . "/";
    }

    private function processPattern(string $route): string {
        $route = preg_replace("/<d(\+*)>/i", "([0-9]\$1)", $route) . "\n";
        $route = preg_replace("/<w(\+*)>/i", "([a-zA-Z0-9-_\.]\${1})", $route) . "\n";
        return trim($route);
    }

    public function createAbsoluteUrl($url) {
        if (($pos = strpos($url, "/") === false) || $pos > 0) {
            $url = "/" . $url;
        }

        return Cux::getInstance()->request->getBaseURL() . $url;
    }

}
