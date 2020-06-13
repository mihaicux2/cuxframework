<?php

/**
 * CuxOutTraffic class file
 * 
 * @package Components
 * @subpackage Traffic
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\components\traffic;

use CuxFramework\utils\CuxBase;

/**
 * SImple class that prints all the current request details
 */
class CuxOutTraffic extends CuxTraffic {

    /**
     * Setup object instance properties
     * @param array $config
     */
    public function config(array $config) {
        parent::config($config);
    }

    /**
     * Process/store current request details
     * Prints the request details
     */
    public function logRequest() {
        
        if ($this->ignoreRequest())
            return;
        
        print_r($this->getVisitorsInfo());
    }

}
