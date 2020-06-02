<?php

/**
 * CuxBase class file
 */

namespace CuxFramework\utils;

/**
 * Base framework class
 */
class CuxBase {

    public static function config(&$object, $properties) {
        foreach ($properties as $name => $value) {
            $object->$name = $value;
        }

        return $object;
    }

}
