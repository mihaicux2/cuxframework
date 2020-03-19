<?php

namespace CuxFramework\utils;

class CuxBase {

    public static function config(&$object, $properties) {
        foreach ($properties as $name => $value) {
            $object->$name = $value;
        }

        return $object;
    }

}
