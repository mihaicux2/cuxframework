<?php

namespace CuxFramework\utils;

abstract class CuxSingleton {

    private static $_instances = array();

    protected function __construct() {
        
    }

    abstract public static function config(array $config);

    final public static function getInstance(): CuxSingleton {
        $calledClass = get_called_class();

        if (!isset(self::$_instances[$calledClass])) {
            self::$_instances[$calledClass] = new $calledClass();
        }

        return self::$_instances[$calledClass];
    }

    private final function __clone() {
        throw new \Exception("Folosind Singleton, clonarea obiectelor este interzisa!", 503);
    }

    private final function __wakeup() {
        throw new Exception("Folosind Singleton, deserializarea obiectelor este interzisa!", 503);
    }
    
    // AES 256 decrypt
    protected function decrypt($edata, $password) {
        $data = base64_decode($edata);
        $salt = substr($data, 0, 16);
        $ct = substr($data, 16);

        $rounds = 3; // depends on key length
        $data00 = $password . $salt;
        $hash = array();
        $hash[0] = hash('sha256', $data00, true);
        $result = $hash[0];
        for ($i = 1; $i < $rounds; $i++) {
            $hash[$i] = hash('sha256', $hash[$i - 1] . $data00, true);
            $result .= $hash[$i];
        }
        $key = substr($result, 0, 32);
        $iv = substr($result, 32, 16);

        return unserialize(openssl_decrypt($ct, 'AES-256-CBC', $key, true, $iv));
    }

    // AES 256 encrypt
    protected function encrypt($data, $password) {
        
        $data = serialize($data);
        
//        // Set a random salt
//        $salt = openssl_random_pseudo_bytes(16);
        // Set a random salt
        $salt = "klM1$%#@!F@#N.:]";

        $salted = '';
        $dx = '';
        // Salt the key(32) and iv(16) = 48
        while (strlen($salted) < 48) {
            $dx = hash('sha256', $dx . $password . $salt, true);
            $salted .= $dx;
        }

        $key = substr($salted, 0, 32);
        $iv = substr($salted, 32, 16);

        $encrypted_data = openssl_encrypt($data, 'AES-256-CBC', $key, true, $iv);
        return base64_encode($salt . $encrypted_data);
    }

}
