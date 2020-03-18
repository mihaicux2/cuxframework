<?php

namespace CuxFramework\components\messages;

use CuxFramework\utils\CuxSingleton;
use CuxFramework\utils\CuxBase;
use CuxFramework\utils\Cux;

class CuxFileMessages extends CuxBaseMessages {
    
    private $_messages = array();
    
    public $messagesPath = "";
    
    public function config(array $config) {
        parent::config($config);
    }
    
    private function loadMessages($lang){
        
        $messages = Cux::getInstance()->cache->get("messages.$lang");
        if (!$messages){
            $messages = array();
            $this->_messages[$lang] = $messages;
            
             // load base messages
            $langFile = "vendor/mihaicux/cuxframework/src/CuxFramework/components/messages/i18n/{$lang}.php";
            
            if (file_exists($langFile) && is_readable($langFile)){
                $messages = array_merge($messages, require($langFile));
            }
            
            // load custom messages
            if ($this->messagesPath){
                $langFile2 = $this->messagesPath."/{$lang}.php";
                if (file_exists($langFile2) && is_readable($langFile2)){
                    $messages = array_merge_recursive($messages, require($langFile2));
                }
            }
            
            Cux::getInstance()->cache->set("messages.$lang", $messages, 3600);
        }
        
        $this->_messages[$lang] = $messages;
    }
    
    public function translate($category, $message, $lang, $context){
        
        if (!$lang){
            $lang = $this->_lang;
        }
        
        $this->loadMessages($lang);
        
        if (isset($this->_messages[$lang]) && isset($this->_messages[$lang][$category]) && isset($this->_messages[$lang][$category][$message])){
            return $this->_messages[$lang][$category][$message];
        } else {
            Cux::getInstance()->raiseEvent("missingTranslation", array(
                "category" => $category,
                "message" => $message,
                "lang" => $lang,
                "context" => $context
            ));
            return $message;
        }
        
    }
    
}
    