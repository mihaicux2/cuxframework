<?php

namespace CuxFramework\components\messages;

use CuxFramework\utils\CuxBaseObject;
use CuxFramework\utils\Cux;

abstract class CuxBaseMessages extends CuxBaseObject {
    
    private $_lang;
    
    public function config(array $config) {
        parent::config($config);
        
        $this->_lang = Cux::getInstance()->language;
    }
    
    abstract public function translate($category, $message, $lang, $context): string;
    
    abstract public function getAllMessages(): array;
    
    abstract public function getLocaleMessages(string $lang): array;
    
    
}
    