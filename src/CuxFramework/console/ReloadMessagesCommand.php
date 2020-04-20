<?php

namespace CuxFramework\console;

use CuxFramework\utils\Cux;
use CuxFramework\console\CuxCommand;

class ReloadMessagesCommand extends CuxCommand{
    
    
    public function run(array $args) {
        
        if (!count($args)){
            die("Please, privide the language list".PHP_EOL);
        }
        
        foreach ($args as $lang){            
            echo $this->getColoredString("Reloadig message cache for: {$lang}", "light_green", "black").PHP_EOL;
            
            Cux::getInstance()->cache->delete("messages.$lang");
        }
        
        echo $this->getColoredString("DONE!", "green", "light_gray").PHP_EOL;
        
    }

}