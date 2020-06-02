<?php

/**
 * CuxExceptionHandler class file
 */

namespace CuxFramework\components\exception;

use CuxFramework\utils\Cux;
use CuxFramework\utils\CuxBaseObject;
use CuxFramework\components\log\CuxLogger;

/**
 * PHP Error/Exception handler
 */
class CuxExceptionHandler extends CuxBaseObject{
    
    /**
     * Setup class instance properties & error&exception handlers
     * @param array $config The list of class instance properties to be set
     */
    public function config(array $config) {
        parent::config($config);
        
        set_exception_handler(array($this, "handleException"));
        set_error_handler(array($this, "handleError"));
        
    }
    
    /**
     * Error handler
     * @param int $code The error code
     * @param string $message The error message
     * @param string $file The file that triggered the error
     * @param int $line The line in the file that triggered the error
     * @return mixed
     * @throws \ErrorException
     */
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
    
    /**
     * Handle all the errors/exceptions raised/thrown by the running code
     * @param mixed (Exception, TypeError, etc.) $ex
     */
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
        
        Cux::log(CuxLogger::ERROR, $ex->getMessage(), $exArray);
        
        if (Cux::getInstance()->isWebApp() && Cux::getInstance()->hasComponent("layout")){
            Cux::getInstance()->layout->setPageTitle(Cux::translate("core.errors", "Error/exception", array(), "Page title for error pages")." #".$ex->getCode());
        }
        
        if (Cux::getInstance()->isWebApp() && Cux::getInstance()->hasComponent("request") && Cux::getInstance()->request->isAjax()){
            echo $ex->getMessage();
        }
        else{
            if ($ex->getCode() == 401){ // login required
                Cux::getInstance()->user->setFlashMessage("info", Cux::translate("core.errors", "Login required!", array(), "Message shown on pages with insufficient privileges"));
                Cux::getInstance()->user->setFlashMessage("httpStatus", $ex->getCode());
                Cux::getInstance()->redirect("/login");
            }

            if (Cux::getInstance()->isWebApp() && Cux::getInstance()->hasComponent("layout")){
                if (Cux::getInstance()->debug){
                    echo Cux::getInstance()->layout->render("//errors/errorDebug", array("ex"=>$ex));
                }
                else{
                    echo Cux::getInstance()->layout->render("//errors/error", array("ex"=>$ex));
                }
            } else {
                print_r($exArray);
            }
        }
    }
    
}

