<?php

namespace CuxFramework\components\messages;

use CuxFramework\utils\CuxSingleton;
use CuxFramework\utils\CuxBase;
use CuxFramework\utils\Cux;

class CuxFileMessages extends CuxBaseMessages {
    
    private $_messages = array();
    
    public $messagesPath = "";
    
    public static function config(array $config): void {
        $ref = static::getInstance();
        CuxBase::config($ref, $config);
    }
    
    private function loadMessages($lang){
        
        $messages = Cux::getInstance()->cache->get("messages.$lang");
        if (!$messages){
            $messages = array();
            $this->_messages[$lang] = $messages;
            
             // load base messages
            $langFile = "vendor/cux/cuxframework/src/CuxFramework/components/messages/i18n/{$lang}.php";
            
            if (file_exists($langFile) && is_readable($langFile)){
                $messages = array_merge($messages, require($langFile));
            }
            
            // load custom messages
            if ($this->messagesPath){
                $langFile2 = $this->messagesPath."/{$lang}.php";
                if (file_exists($langFile2) && is_readable($langFile2)){
                    $messages = array_merge($messages, require($langFile2));
                }
            }
            
            Cux::getInstance()->cache->set("messages.$lang", $messages, 3600);
        }
        
        $this->_messages[$lang] = $messages;
    }
    
    public function translate($category, $message, $lang){
        
        if (!$lang){
            $lang = $this->_lang;
        }
        
        $this->loadMessages($lang);
        
        return (isset($this->_messages[$lang]) && isset($this->_messages[$lang][$category]) && isset($this->_messages[$lang][$category][$message])) ? $this->_messages[$lang][$category][$message] : $message;
        
    }
    
}
    