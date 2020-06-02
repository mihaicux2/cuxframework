<?php

/**
 * CuxAPCCache class file
 */

namespace CuxFramework\components\cache;

use CuxFramework\utils\CuxBase;
use CuxFramework\utils\Cux;

/**
 * Cache class using PHP apcu extension
 */
class CuxAPCCache extends CuxCache {

    /**
     * Setup instance properties
     * @param array $config The list of instance properties to be set
     * @throws \Exception
     */
    public function config(array $config) {
        parent::config($config);
        $extension = "apcu";
        if (!extension_loaded($extension)) {
            throw new \Exception(Cux::translate("core.errors", "Extension not found: {extension}", array("{extension}" => $extension), "Error shown on missing extension"), 503);
        }
    }

    /**
     * Checks wether the cache contains a specific key
     * @param string $key a unique key identifying the cached value
     * @return boolean true if the cache contains the given key
     */
    public function exists(string $key): bool {
        return apcu_exists($this->buildKey($key));
    }

    /**
     * Retrieves a value from cache with a specified key.
     * @param string $key a unique key identifying the cached value
     * @return string|boolean the value stored in cache, false if the value is not in the cache or expired.
     */
    public function get(string $key) {
        return apcu_fetch($this->buildKey($key));
    }

    /**
     * Retrieves multiple values from cache with the specified keys.
     * @param array $keys a list of keys identifying the cached values
     * @return array a list of cached values indexed by the keys
     */
    public function getValues(array $keys): array {
        $values = apcu_fetch($this->buildKeys($keys));
        return is_array($values) ? $values : [];
    }

    /**
     * Stores a value identified by a key in cache.     *
     * @param string $key the key identifying the value to be cached
     * @param string $value the value to be cached
     * @param integer $duration the number of seconds in which the cached value will expire. 0 means never expire.
     * @return boolean true if the value is successfully stored into cache, false otherwise
     */
    public function set(string $key, $value, int $duration): bool {
        return apcu_store($this->buildKey($key), $value, $duration);
    }

    /**
     * Stores multiple key-value pairs in cache.
     * @param array $data array where key corresponds to cache key while value
     * @param integer $duration the number of seconds in which the cached values will expire. 0 means never expire.
     * @return array list of failed keys
     */
    public function setValues(array $data, int $duration): array {
        $hashedData = array();
        foreach ($data as $key => $value) {
            $hashedData[$this->buildKey($key)] = $value;
        }
        $result = apcu_store($hashedData, null, $duration);
        return is_array($result) ? array_keys($result) : [];
    }

    /**
     * Stores a value identified by a key into cache if the cache does not contain this key.
     * @param string $key the key identifying the value to be cached
     * @param string $value the value to be cached
     * @param integer $duration the number of seconds in which the cached value will expire. 0 means never expire.
     * @return boolean true if the value is successfully stored into cache, false otherwise
     */
    public function add(string $key, $value, int $duration): bool {
        return apcu_add($this->buildKey($key), $value, $duration);
    }

    /**
     * Adds multiple key-value pairs to cache.
     * @param array $data array where key corresponds to cache key while value is the value stored
     * @param integer $duration the number of seconds in which the cached values will expire. 0 means never expire.
     * @return array list of failed keys
     */
    public function addValues(array $data, int $duration): array {
        $hashedData = array();
        foreach ($data as $key => $value) {
            $hashedData[$this->buildKey($key)] = $value;
        }
        $result = apcu_add($hashedData, null, $duration);
        return is_array($result) ? array_keys($result) : [];
    }

    /**
     * Deletes a value with the specified key from cache
     * @param string $key the key of the value to be deleted
     * @return boolean if no error happens during deletion
     */
    public function delete(string $key): bool {
        return apcu_delete($this->buildKey($key));
    }

    /**
     * Deletes all values from cache.
     * @return boolean whether the flush operation was successful.
     */
    public function flush(): bool {
        return apcu_clear_cache();
    }

}
