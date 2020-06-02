<?php

namespace CuxFramework\modules\cux\controllers;

use CuxFramework\utils\Cux;
use CuxFramework\components\user\CuxUser;
use CuxFramework\utils\CuxBaseObject;
use CuxFramework\console;

class ToolsController extends CuxController {

    public function beforeAction(string $actionName): bool {
        if (!isset($_SERVER['PHP_AUTH_USER']) || !$this->checkLogin()) {
            header('WWW-Authenticate: Basic realm="My Realm"');
            header('HTTP/1.0 401 Unauthorized');
//            throw new \Exception("Login required!", 401);
            echo "Login required!";
            return false;
        }
        return true;
    }

    private function checkLogin(){
        return $_SERVER['PHP_AUTH_USER'] == Cux::getInstance()->getParam("commandsUser") && $_SERVER['PHP_AUTH_PW'] == Cux::getInstance()->getParam("commandsPass");
    }
    
    private function loadAvailableCommands(){
        $availableCommands = Cux::getInstance()->cache->get("app.array.availableCommands");
        if (!$availableCommands){
            
            $frameworkPath = Cux::getFrameworkPath();
            $frameworkFiles = scandir($frameworkPath."console");
            $frameworkCommands = array_splice($frameworkFiles, 2); // remove "." & "..";
            $frameworkCommands = array_map(function($name) use ($frameworkPath){
                return array(
                    "name" => lcfirst(mb_substr($name, 0, -11)), // remove "Command.php"
                    "fullPath" => $frameworkPath.$name,
                    "coreCommand" => true
                );
            }, $frameworkCommands);
            
            $localPath = "console";
            $localFiles = scandir($localPath);
            $localCommands = array_splice($localFiles, 2); // remove "." & "..";
            $localCommands = array_map(function($name) use ($localPath){
                return array(
                    "name" => lcfirst(mb_substr($name, 0, -11)), // remove "Command.php"
                    "fullPath" => $localPath.DIRECTORY_SEPARATOR.$name,
                    "coreCommand" => false
                );
            }, $localCommands);

            // ignore the "cux" command :)
            $availableCommands = array_filter(array_merge($frameworkCommands, $localCommands), function($command){
                return $command["name"] != "cux";
            });       
            
            Cux::getInstance()->cache->set("app.array.availableCommands", $availableCommands, 600);
        }
        return $availableCommands;
    }
    
    public function actionCommands($params) {
        
        echo $this->render("commands", array(
            "commands" => $this->loadAvailableCommands()
        ));
        
    }
    
    public function actionGetCommandHelp($params){
        $cmd = Cux::getInstance()->request->getParam("cmd", "");
        
        $commandA = $command = "console\\".ucfirst($cmd)."Command"; // check in the project directory
        $commandB = "CuxFramework\\console\\".ucfirst($cmd)."Command"; // check in the framework directory

        if (class_exists($commandA) && is_subclass_of($commandA, "CuxFramework\utils\CuxBaseObject")){
            $command = $commandA;
        }
        elseif(class_exists($commandB) && is_subclass_of($commandB, "CuxFramework\utils\CuxBaseObject")){
            $command = $commandB;
        }
        else {
            throw new \Exception("Command not found", 404);
        }
        
        $args = $_GET;
        $args["cmd"] = null;
        unset($args["cmd"]);
        
        $commandInstance = new $command();
        echo nl2br(str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $commandInstance->help()));
    }
    
    public function actionExecuteCommand($params){
        $cmd = Cux::getInstance()->request->getPost("cmd", "");
        $args = Cux::getInstance()->request->getPost("args", "");
        
        if ($args){
            $args = explode(" ", $args);
        } else {
            $args = array();
        }
        
        $commandA = $command = "console\\".ucfirst($cmd)."Command"; // check in the project directory
        $commandB = "CuxFramework\\console\\".ucfirst($cmd)."Command"; // check in the framework directory
        
        if (class_exists($commandA) && is_subclass_of($commandA, "CuxFramework\utils\CuxBaseObject")){
            $command = $commandA;
        }
        elseif(class_exists($commandB) && is_subclass_of($commandB, "CuxFramework\utils\CuxBaseObject")){
            $command = $commandB;
        }
        else {
            throw new \Exception("Command not found", 404);
        }
        
        $commandInstance = new $command();
        echo nl2br($commandInstance->run($args));
    }
    
}
