<?php

/**
 * CuxFileMessages class file
 * 
 * @package Components
 * @subpackage Messages
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\components\messages;

use CuxFramework\utils\CuxSingleton;
use CuxFramework\utils\CuxBase;
use CuxFramework\utils\Cux;

/**
 * Simple i18n class that uses files to store and load translated messages
 */
class CuxFileMessages extends CuxBaseMessages {
    
    /**
     * The list of ( to be- )translated messages
     * @var array
     */
    private $_messages = array();
    
    /**
     * The relative path for the translated messages
     * @var string
     */
    public $messagesPath = "";
    
    /**
     * Setup object instance properties
     * @param array $config
     */
    public function config(array $config) {
        parent::config($config);
    }
    
    /**
     * Load translated messages for a given language
     * @param string $lang
     */
    private function loadMessages(string $lang){
        
        $messages = Cux::getInstance()->cache->get("messages.$lang");
        
        if (!$messages){
            $messages = array();
            $this->_messages[$lang] = $messages;
            
             // load base messages
            $langFile = Cux::getFrameworkPath()."components/messages/i18n/{$lang}.php";
            
            if (file_exists($langFile) && is_readable($langFile)){
                $messages = array_merge($messages, require($langFile));
            }
            
            // load custom messages
            if ($this->messagesPath){
                $langFile2 = $this->messagesPath."/{$lang}.php";
                if (file_exists($langFile2) && is_readable($langFile2)){
//                    $messages = array_merge_recursive($messages, require($langFile2));
                    $messages = array_merge($messages, require($langFile2));
                }
            }
            
            Cux::getInstance()->cache->set("messages.$lang", $messages, 3600);
        }
        
        $this->_messages[$lang] = $messages;
        
    }
    
    /**
     * Get all the translated messages
     * @return array
     */
    public function getAllMessages(): array{
        return $this->_messages;
    }
    
    /**
     * Get the translated messages for a given language
     * @param string $lang
     * @return array
     */
    public function getLocaleMessages(string $lang): array {
        return isset($this->_messages[$locale]) ? $this->_messages[$locale] : array();
    }
    
    /**
     * Translate a given message
     * @param string $category The category of messages for the current text to be translated
     * @param string $message The text to be translated
     * @param string $lang The language for the translation
     * @param mixed $context The context for the current message
     */
    public function translate($category, $message, $lang, $context): string{
        
        if (!$lang){
            $lang = $this->_lang;
        }
        
        $this->loadMessages($lang);
        
        if (isset($this->_messages[$lang]) && isset($this->_messages[$lang][$category]) && isset($this->_messages[$lang][$category][$message]) && $this->_messages[$lang][$category][$message]){
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
    