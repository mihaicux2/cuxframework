<?php

namespace CuxFramework\console;

use CuxFramework\utils\Cux;
use CuxFramework\console\CuxCommand;

class ReloadMessagesCommand extends CuxCommand{
    
    
    public function run(array $args) {
        
        if (!count($args)){
            die("Please, privide the language list".PHP_EOL);
        }
        
        $this->parseArguments($args);
        
        foreach ($args as $lang){            
            echo $this->getColoredString("Reloadig message cache for: {$lang}", "light_green", "black").PHP_EOL;
            
            Cux::getInstance()->cache->delete("messages.$lang");
        }
        
        echo $this->getColoredString("DONE!", "green", "light_gray").PHP_EOL;
        
    }
    
    public function help(): string{
         $str = "";
        
        $str .= $this->getColoredString("                  ReloadMessages Command                    ", "light_green", "black").PHP_EOL.PHP_EOL;
        $str .= $this->getColoredString("    This command is used to clear the messages/translations cache    ", "blue", "yellow").PHP_EOL;
        $str .= "You must provide the list of languages to be reloaded".PHP_EOL.PHP_EOL;
        $str .= "Usage example: ./maintenance reloadMessages en ro".PHP_EOL;
        
        return $str;
    }

}