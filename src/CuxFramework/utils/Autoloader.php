<?php

/**
 * Autoloader class file
 * 
 * @package Utils
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\utils;

/**
 * Framework auto-loader class
 */
class Autoloader {

    /**
     * Static method used to load a given class, based on it's namespace
     * @param string $className
     * @return boolean
     */
    static public function loader($className) {
        $filename = str_replace('\\', '/', $className) . ".php";
        if (file_exists($filename)) {
            include($filename);
            if (class_exists($className, false) || interface_exists($className, false) || trait_exists($className, false)) {
                return TRUE;
            }
        }
        return FALSE;
    }

}
