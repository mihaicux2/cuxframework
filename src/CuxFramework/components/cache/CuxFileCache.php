<?php

namespace CuxFramework\components\cache;

use CuxFramework\utils\CuxBase;
use CuxFramework\utils\Cux;

/**
 * Cache class that uses the file system to store data
 */
class CuxFileCache extends CuxCache {

    private $_memcache;
    public $cacheDir = "./cache";
    public $cacaheLifetime = 3600;

    public function config(array $config) {
        parent::config($config);
    }

    /**
     * Fetches a directory to store the cache data
     *
     * @param string $key The directory name
     * @return string
     */
    protected function getDirectory($key) {
        $hash = sha1($key, false);
        $dirs = array(
            $this->getCacheDirectory(),
            substr($hash, 0, 2),
            substr($hash, 2, 2)
        );
        return join(DIRECTORY_SEPARATOR, $dirs);
    }

    /**
     * Fetches a base directory to store the cache data
     * @return string
     */
    protected function getCacheDirectory() {
        return $this->cacheDir;
    }

    /**
     * Fetches a file path of the cache data
     *
     * @param string $key
     * @return string
     */
    protected function getFileName($key) {
        $directory = $this->getDirectory($key);
        $hash = sha1($key, false);
        $file = $directory . DIRECTORY_SEPARATOR . $hash . '.cache';
        return $file;
    }

    /**
     * Reads data from a given file
     * @param type $key
     * @return mixed
     */
    public function readFile(string $key) {
        $cachePath = $this->getFileName($key);

        $lines = file($cachePath);
        $lifetime = array_shift($lines);
        $lifetime = (int) trim($lifetime);

        if ($lifetime !== 0 && $lifetime < time()) {
            @unlink($file_name);
            return false;
        }

        $serialized = join('', $lines);
        $data = unserialize($serialized);
        return $data;
    }

    /**
     * Deletes a directory, recursively
     * @param string $dir
     * @return boolean True if the whole directory has been deleted
     */
    protected function delTree($dir) {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
    
    /**
     * Checks wether the cache contains a specific key
     * @param string $key a unique key identifying the cached value
     * @return boolean true if the cache contains the given key
     */
    public function exists(string $key): bool {
//        return true;
        $cachePath = $this->getFileName($key);
        return file_exists($cachePath) && is_readable($cachePath);
    }
    
    /**
     * Retrieves a value from cache with a specified key.
     * @param string $key a unique key identifying the cached value
     * @return string|boolean the value stored in cache, false if the value is not in the cache or expired.
     */
    public function get(string $key) {
        if (!$this->exists($key))
            return false;
        return $this->readFile($key);
    }

    /**
     * Retrieves multiple values from cache with the specified keys.
     * @param array $keys a list of keys identifying the cached values
     * @return array a list of cached values indexed by the keys
     */
    public function getValues(array $keys): array {
        $ret = array();
        foreach ($keys as $key){
            $ret[$key] = $this->get($key);
        }
        return $ret;
    }

    /**
     * Stores a value identified by a key in cache.     *
     * @param string $key the key identifying the value to be cached
     * @param string $value the value to be cached
     * @param integer $duration the number of seconds in which the cached value will expire. 0 means never expire.
     * @return boolean true if the value is successfully stored into cache, false otherwise
     */
    public function set(string $key, $value, int $duration): bool {
        if (!$duration) {
            $duration = $this->cacaheLifetime;
        }

        $dir = $this->getDirectory($key);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                return false;
            }
        }

        $cachePath = $this->getFileName($key);

        $duration = time() + $duration;
        $serialized = serialize($value);
        $result = file_put_contents($cachePath, $duration . PHP_EOL . $serialized);
        if ($result === false) {
            return false;
        }
        return true;
    }

    /**
     * Stores multiple key-value pairs in cache.
     * @param array $data array where key corresponds to cache key while value
     * @param integer $duration the number of seconds in which the cached values will expire. 0 means never expire.
     * @return array list of failed keys
     */
    public function setValues(array $data, int $duration): array {
        $ret = array();
        foreach ($data as $key => $value){
            if (!$this->set($key, $value, $duration)){
                $ret[] = $key;
            }
        }
        return $ret;
    }

    /**
     * Stores a value identified by a key into cache if the cache does not contain this key.
     * @param string $key the key identifying the value to be cached
     * @param string $value the value to be cached
     * @param integer $duration the number of seconds in which the cached value will expire. 0 means never expire.
     * @return boolean true if the value is successfully stored into cache, false otherwise
     */
    public function add(string $key, $value, int $duration): bool {
        return $this->set($key, $value, $duration);
    }

    /**
     * Adds multiple key-value pairs to cache.
     * @param array $data array where key corresponds to cache key while value is the value stored
     * @param integer $duration the number of seconds in which the cached values will expire. 0 means never expire.
     * @return array list of failed keys
     */
    public function addValues(array $data, int $duration): array {
        $ret = array();
        foreach ($data as $key => $value){
            if (!$this->add($key, $value, $duration)){
                $ret[] = $key;
            }
        }
        return $ret;
    }
    
    /**
     * Deletes a value with the specified key from cache
     * @param string $key the key of the value to be deleted
     * @return boolean if no error happens during deletion
     */
    public function delete(string $key): bool {
        $cachePath = $this->getFileName($key);
        return @unlink($cachePath);
    }

    /**
     * Deletes all values from cache.
     * @return boolean whether the flush operation was successful.
     */
    public function flush(): bool {
        return $this->delTree($this->getCacheDirectory());
    }

}
