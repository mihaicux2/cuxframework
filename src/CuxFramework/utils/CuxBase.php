<?php

/**
 * CuxBase class file
 */

namespace CuxFramework\utils;

/**
 * Base framework class
 */
class CuxBase {

    /**
     * Setup $object properties
     * @param mixed $object The object to be configured 
     * @param array $properties The list of properties
     * @return mixed The $object itself. This is useless, as the input parameter is sent by reference :)))
     */
    public static function config(&$object, array $properties = array()) {
        foreach ($properties as $name => $value) {
            $object->$name = $value;
        }

        return $object;
    }

}
