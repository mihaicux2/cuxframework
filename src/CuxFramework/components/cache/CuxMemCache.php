<?php

namespace CuxFramework\components\cache;

use CuxFramework\utils\CuxBase;
use CuxFramework\utils\Cux;

class CuxMemCache extends CuxCache {

    private $_memcache;
    
    public function config(array $config) {
        parent::config($config);
        $extension = "memcache";
        if (!extension_loaded($extension)) {
            throw new \Exception(Cux::translate("error", "Extension not found: {extension}", array(
                "{extension}" => $extension
            )), 503);
        }
        $this->_memcache = new \Memcache();
        if (is_array($this->servers) && !empty($this->servers)) {
            foreach ($this->servers as $server) {
                $this->_memcache->addServer($server["host"], $server["port"]);
            }
        }
    }

    protected function buildKey(string $key): string {
        return $this->keyPrefix . $this->encrypt($key, $this->key);
    }

    private function buildKeys(array $keys) {
        $ret = array();
        foreach ($keys as $key) {
            $ret[] = $this->buildKey($key);
        }
        return $ret;
    }

    public function exists(string $key): bool {
        return true;
    }

    public function get(string $key) {
        return $this->_memcache->get($this->buildKey($key));
    }

    public function getValues(array $keys): array {
        $values = $this->_memcache->fetchAll($this->buildKeys($keys));
        return is_array($values) ? $values : [];
    }

    public function set(string $key, $value, int $duration): bool {
        return $this->_memcache->set($this->buildKey($key), $value, MEMCACHE_COMPRESSED, $duration);
    }

    public function setValues(array $data, int $duration): array {
        $hashedData = array();
        foreach ($data as $key => $value) {
            $hashedData[$this->buildKey($key)] = $value;
        }
        $result = $this->_memcache->setMulti($hashedData, $duration);
        return $result ? array_keys($data) : [];
    }

    public function add(string $key, $value, int $duration): bool {
        return $this->_memcache->add($this->buildKey($key), $value, MEMCACHE_COMPRESSED, $duration);
    }

    public function addValues(array $data, int $duration): array {
        $result = false;
        foreach ($data as $key => $value) {
            $ok = $this->add($key, $value, $duration);
            if ($ok){
                $result[$key] = true;
            }
        }
        return is_array($result) ? array_keys($result) : [];
    }

    public function delete(string $key): bool {
        return $this->_memcache->delete($this->buildKey($key));
    }

    public function flush(): bool {
        return $this->_memcache->flush();
    }

} 