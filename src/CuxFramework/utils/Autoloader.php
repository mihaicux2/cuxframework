<?php

namespace CuxFramework\utils;

class Autoloader {

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
