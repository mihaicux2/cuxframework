<?php

/**
 * CuxBaseMessages abstract class file
 * 
 * @package Components
 * @subpackage Messages
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\components\messages;

use CuxFramework\utils\CuxBaseObject;
use CuxFramework\utils\Cux;

/**
 * Abstract class to be used as a base for future Internationalization (i18n) support
 */
abstract class CuxBaseMessages extends CuxBaseObject {
    
    /**
     * The current application's language
     * @var string 
     */
    private $_lang;
    
    /**
     * Setup the object instance properties
     * @param array $config
     */
    public function config(array $config) {
        parent::config($config);
        
        $this->_lang = Cux::getInstance()->language;
    }
    
    /**
     * Abstract method that needs to be implemented by extending classes.
     * Use this method to actually translated messages to a given language
     * @param string $category The category of messages for the current text to be translated
     * @param string $message The text to be translated
     * @param string $lang The language for the translation
     * @param mixed $context The context for the current message
     */
    abstract public function translate(string $category, string $message, string $lang, string $context): string;
    
    /**
     * Abstract method that needs to be implemented by extending classes.
     * Use this method to retrieve all the translated messages
     * @return array
     */
    abstract public function getAllMessages(): array;
    
    /**
     * Abstract method that needs to be implemented by extending classes.
     * Use this method to retrieve all the translated messages for a given language
     * @para, string $lang The language for the translated messages
     * @return array
     */
    abstract public function getLocaleMessages(string $lang): array;
    
    
}
    