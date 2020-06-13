<?php

/**
 * CuxCache abstract class file
 * 
 * @package Components
 * @subpackage Cache
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\components\cache;

use CuxFramework\utils\CuxBaseObject;

/**
 * Base class for the caching system
 */
abstract class CuxCache  extends CuxBaseObject {
    
    /**
     * The encryption key that will be used for the cache key mapping/encryption
     * @var string
     */
    public $key = "defaultEncryptionKey"; // you should change this
    
    /**
     * Prefix all cache keys with this
     * @var string
     */
    public $keyPrefix = "";
    
    /**
     * Encrypt the cache keys
     * @var type 
     */
    public $useEncryption = true;
    
    /**
     * Default cache life time
     * @var int
     */
    public $lifeTime = 3600;
    
    /**
     * Using the predefined $keyPrefix prefix and $key encryption key, builds an encrypted version of a given (string) key
     * @param string $key The key to be mapped/encrypted
     * @return string
     */
    protected function buildKey(string $key): string {
        return $this->keyPrefix . $this->encrypt($key, $this->key);
    }
    
    /**
     * Builds mapped/encrypted keys from a given array of string keys
     * @param array $keys The list of keys to be mapped/encrypted
     * @return type
     */
    protected function buildKeys(array $keys) {
        $ret = array();
        foreach ($keys as $key) {
            $ret[] = $this->buildKey($key);
        }
        return $ret;
    }
    
    /**
     * Checks wether the cache contains a specific key
     * @param string $key a unique key identifying the cached value
     * @return boolean true if the cache contains the given key
     */
    abstract public function exists(string $key): bool;
    
    /**
     * Retrieves a value from cache with a specified key.
     * @param string $key a unique key identifying the cached value
     * @return string|boolean the value stored in cache, false if the value is not in the cache or expired.
     */
    abstract public function get(string $key);
    
    /**
     * Retrieves multiple values from cache with the specified keys.
     * @param array $keys a list of keys identifying the cached values
     * @return array a list of cached values indexed by the keys
     */
    abstract public function getValues(array $keys): array;
    
    /**
     * Stores a value identified by a key in cache.     *
     * @param string $key the key identifying the value to be cached
     * @param string $value the value to be cached
     * @param integer $duration the number of seconds in which the cached value will expire. 0 means never expire.
     * @return boolean true if the value is successfully stored into cache, false otherwise
     */
    abstract public function set(string $key, $value, int $duration = null): bool;
    
    /**
     * Stores multiple key-value pairs in cache.
     * @param array $data array where key corresponds to cache key while value
     * @param integer $duration the number of seconds in which the cached values will expire. 0 means never expire.
     * @return array list of failed keys
     */
    abstract public function setValues(array $data, int $duration = null): array;
    
    /**
     * Stores a value identified by a key into cache if the cache does not contain this key.
     * @param string $key the key identifying the value to be cached
     * @param string $value the value to be cached
     * @param integer $duration the number of seconds in which the cached value will expire. 0 means never expire.
     * @return boolean true if the value is successfully stored into cache, false otherwise
     */
    abstract public function add(string $key, $value, int $duration): bool;
    
    /**
     * Adds multiple key-value pairs to cache.
     * @param array $data array where key corresponds to cache key while value is the value stored
     * @param integer $duration the number of seconds in which the cached values will expire. 0 means never expire.
     * @return array list of failed keys
     */
    abstract public function addValues(array $data, int $duration): array;
    
    /**
     * Deletes a value with the specified key from cache
     * @param string $key the key of the value to be deleted
     * @return boolean if no error happens during deletion
     */
    abstract public function delete(string $key): bool;
    
    /**
     * Deletes all values from cache.
     * @return boolean whether the flush operation was successful.
     */
    abstract public function flush(): bool;
    
    /**
     * Decrypts an encrypted string
     * @param string $edata The encrypted data
     * @param string $password The encryption key
     * @return string
     */
    protected function decrypt($edata, $password) {
        if ($this->useEncryption){
            return parent::decrypt($edata, $password);
        }
        return $edata;
    }

    /**
     * Encrypts a given plain string
     * @param string $data The data to be encrypted
     * @param type $password The encryption key
     * @return string
     */
    protected function encrypt($data, $password) {
        if ($this->useEncryption){
            return parent::encrypt($data, $password);
        }
        return $data;
    }
    
}

