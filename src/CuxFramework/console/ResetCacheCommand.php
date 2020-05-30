<?php

namespace CuxFramework\console;

use CuxFramework\utils\Cux;
use CuxFramework\console\CuxCommand;

class ResetCacheCommand extends CuxCommand{
    
    
    public function run(array $args) {
        
        echo $this->getColoredString("Reseting cache...", "light_green", "black");
        
        Cux::getInstance()->cache->flush();
        
        echo $this->getColoredString("DONE!", "green", "light_gray").PHP_EOL;
        
    }

    public function help(): string{
         $str = "";
        
        $str .= $this->getColoredString("                  ResetCache Command                    ", "light_green", "black").PHP_EOL.PHP_EOL;
        $str .= $this->getColoredString("    This command is used to clear th cache data   ", "blue", "yellow").PHP_EOL;
        $str .= "No parameters are required for this command".PHP_EOL.PHP_EOL;
        $str .= "Usage example: ./maintenance resetCache".PHP_EOL;
        
        return $str;
    }
    
}