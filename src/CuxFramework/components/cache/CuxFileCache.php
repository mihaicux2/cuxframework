<?php

namespace CuxFramework\components\cache;

use CuxFramework\utils\CuxBase;
use CuxFramework\utils\Cux;

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
     * @param string $key
     *
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
     *
     * @return string
     */
    protected function getCacheDirectory() {
        return $this->cacheDir;
    }

    /**
     * Fetches a file path of the cache data
     *
     * @param string $key
     *
     * @return string
     */
    protected function getFileName($key) {
        $directory = $this->getDirectory($key);
        $hash = sha1($key, false);
        $file = $directory . DIRECTORY_SEPARATOR . $hash . '.cache';
        return $file;
    }

    public function exists(string $key): bool {
//        return true;
        $cachePath = $this->getFileName($key);
        return file_exists($cachePath) && is_readable($cachePath);
    }

    public function readFile($key) {
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

    public function get(string $key) {
        if (!$this->exists($key))
            return false;
        return $this->readFile($key);
//        return $this->_memcache->get($this->buildKey($key));
    }

    public function getValues(array $keys): array {
//        $values = $this->_memcache->fetchAll($this->buildKeys($keys));
//        return is_array($values) ? $values : [];
        return array();
    }

    public function set(string $key, $value, int $duration): bool {
//        return $this->_memcache->set($this->buildKey($key), $value, MEMCACHE_COMPRESSED, $duration);
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

    public function setValues(array $data, int $duration): array {
        return array();
    }

    public function add(string $key, $value, int $duration): bool {
//        return $this->_memcache->add($this->buildKey($key), $value, MEMCACHE_COMPRESSED, $duration);
        return $this->set($key, $value, $duration);
    }

    public function addValues(array $data, int $duration): array {
        return array();
    }

    public function delete(string $key): bool {
//        return $this->_memcache->delete($this->buildKey($key));
        $cachePath = $this->getFileName($key);
        return @unlink($cachePath);
    }

    protected function delTree($dir) {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    public function flush(): bool {
//        return $this->_memcache->flush();
        return $this->delTree($this->getCacheDirectory());
    }

}
