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

}