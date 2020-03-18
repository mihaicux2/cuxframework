<?php

namespace CuxFramework\components\exception;

use CuxFramework\utils\Cux;
use CuxFramework\utils\CuxBaseObject;
use CuxFramework\components\log\CuxLogger;

class CuxExceptionHandler extends CuxBaseObject{
    
    public function config(array $config) {
        parent::config($config);
        
        set_exception_handler(array($this, "handleException"));
        set_error_handler(array($this, "handleError"));
        
    }
    
    public function handleError(int $code, string $message, string $file, int $line){
        if (!(error_reporting() & $code)){
            return null;
        }
        $prefix = "";
        switch ($code){
            case E_ERROR:
                $prefix = "Error: ";
                break;
            case E_WARNING:
                $prefix = "Warning: ";
                break;
            case E_PARSE:
                $prefix = "Parse error: ";
                break;
            case E_NOTICE:
                $prefix = "Notice: ";
                break;
            case E_CORE_ERROR:
                $prefix = "Core error: ";
                break;
            case E_CORE_WARNING:
                $prefix = "Core warning: ";
                break;
            case E_COMPILE_ERROR:
                $prefix = "Compilation error: ";
                break;
            case E_COMPILE_WARNING:
                $prefix = "Compilation warning: ";
                break;
            case E_USER_ERROR:
                $prefix = "Compilation error: ";
                break;
            case E_USER_WARNING:
                $prefix = "User warning: ";
                break;
            case E_USER_NOTICE:
                $prefix = "User notice: ";
                break;
            case E_STRICT:
                $prefix = "Strict declaration error: ";
                break;
            case E_RECOVERABLE_ERROR:
                $prefix = "Recoverable error: ";
                break;
            case E_DEPRECATED:
                $prefix = "Deprecated: ";
                break;
            case E_USER_DEPRECATED:
                $prefix = "User deprecated: ";
                break;
            case E_ALL:
                break;
        }
        throw new \ErrorException($prefix.$message, 504, $code, $file, $line);
    }
    
    // also TypeError & other PHP data types
    public function handleException(/*\Exception*/ $ex){
        
        http_response_code((int)$ex->getCode());
        if (($obLevel = ob_get_level()) > 0){ // renunta la orice era de afisat inainte de eroare
            for ($i = 0; $i < $obLevel; $i++){
                ob_end_clean();
            }
        }
        $exArray = array(
            "code" => $ex->getCode(),
            "message" => $ex->getMessage(),
            "stackTrace" => $ex->getTrace()
        );
        
//        print_r($ex);
//        die();
        
        Cux::getInstance()->logger->log(CuxLogger::ERROR, $ex->getMessage(), $exArray);
        Cux::getInstance()->layout->setPageTitle("Exceptie/eroare #".$ex->getCode());
        
        if (Cux::getInstance()->request->isAjax()){
            echo $ex->getMessage();
        }
        else{
            if ($ex->getCode() == 401){ // login required
                Cux::getInstance()->user->setFlashMessage("info", "Este necesara autentificarea!");
                Cux::getInstance()->user->setFlashMessage("httpStatus", $ex->getCode());
                Cux::getInstance()->redirect("/login");
            }

            if (Cux::getInstance()->debug){
                echo Cux::getInstance()->layout->render("//errors/errorDebug", array("ex"=>$ex));
            }
            else{
                echo Cux::getInstance()->layout->render("//errors/error", array("ex"=>$ex));
            }
        }
    }
    
}

