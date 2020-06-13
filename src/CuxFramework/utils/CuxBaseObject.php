<?php

/**
 * Base class for any extending classes to be instantiated
 * 
 * @package Utils
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\utils;

/**
 * Abstract Class that is set to work as a base/starting point for the framework components
 */
abstract class CuxBaseObject {

    /**
     * Setup class properties using this method
     * @param array $properties The list of properties to be set
     */
    public function config(array $properties) {
        foreach ($properties as $name => $value) {
            $this->$name = $value;
        }
    }

    /**
     * Decrypts an encrypted string (AES-256)
     * @param string $edata The encrypted data
     * @param string $password The encryption key
     * @return string
     */
    protected function decrypt(string $edata, string $password) {
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
        
        // Set a random salt
//        $salt = openssl_random_pseudo_bytes(16);
        $salt = Cux::getInstance()->encryptionSalt;

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
    
    /**
     * Returns the class base name - regardless of the current namespace
     * @return string
     */
    public function getShortName(){
        return (new \ReflectionClass($this))->getShortName();
    }
    
}
