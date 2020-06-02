<?php

/**
 * SIngleton class file
 */

namespace CuxFramework\utils;

/**
 * Class implementing the Singleton pattern 
 */
abstract class CuxSingleton {

    /**
     * The list of loaded classes instances
     * @var array
     */
    private static $_instances = array();

    /**
     * Protected constructor to prevent direct instantiation
     */
    protected function __construct() {
        
    }

    /**
     * Metod to be implemented by extending classes
     */
    abstract public static function config(array $config);

    /**
     * Singleton pattern instance call
     * @return \CuxFramework\utils\CuxSingleton
     */
    final public static function getInstance(): CuxSingleton {
        $calledClass = get_called_class();

        if (!isset(self::$_instances[$calledClass])) {
            self::$_instances[$calledClass] = new $calledClass();
        }

        return self::$_instances[$calledClass];
    }

    /**
     * Prevent instantiation via clone methods
     * @throws \Exception
     */
    private final function __clone() {
        throw new \Exception("Folosind Singleton, clonarea obiectelor este interzisa!", 503);
    }

    /**
     * Prevent instantiation via wake up  methods
     * @throws \Exception
     */
    private final function __wakeup() {
        throw new \Exception("Folosind Singleton, deserializarea obiectelor este interzisa!", 503);
    }
    
    /**
     * Decrypts an encrypted string (AES-256)
     * @param string $edata The encrypted data
     * @param string $password The encryption key
     * @return string
     */
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

    /**
     * Encrypts a given plain string (AES-256)
     * @param string $data The data to be encrypted
     * @param type $password The encryption key
     * @return string
     */
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
