<?php

/**
 * CuxNullTraffic class file
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
 * Simple class that ignores the current request
 */
class CuxNullTraffic extends CuxTraffic {
    
    /**
     * Setup object instance properties
     * @param array $config
     */
    public function config(array $config) {
        parent::config($config);
    }

    /**
     * Process/store current request details
     * Does nothing
     */
    public function logRequest() {}

}