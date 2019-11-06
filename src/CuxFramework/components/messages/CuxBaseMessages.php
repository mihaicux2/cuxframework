<?php

namespace CuxFramework\components\messages;

use CuxFramework\utils\CuxSingleton;
use CuxFramework\utils\CuxBase;

abstract class CuxBaseMessages extends CuxSingleton {
    
    private $_lang;
    
    public static function config(array $config): void {
        $ref = static::getInstance();
        CuxBase::config($ref, $config);
        
        $ref->_lang = Cux::getInstance()->language;
    }
    
    abstract public function translate($category, $message, $lang, $context);
    
}
    