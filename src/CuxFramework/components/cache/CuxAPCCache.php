<?php

namespace CuxFramework\components\cache;

use CuxFramework\utils\CuxBase;

class CuxAPCCache extends CuxCache {

    public function config(array $config) {
        parent::config($config);
        $extension = "apcu";
        if (!extension_loaded($extension)) {
            throw new \Exception("APCCache are nevoia ca extensia de PHP `$extension` sa fie incarcata.", 501);
        }
    }

    protected function buildKey(string $key): string {
        return $this->keyPrefix . $this->encrypt($key, $this->key);
    }

    private function buildKeys(array $keys): array {
        $ret = array();
        foreach ($keys as $key) {
            $ret[] = $this->buildKey($key);
        }
        return $ret;
    }

    public function exists(string $key): bool {
        return apcu_exists($this->buildKey($key));
    }

    public function get(string $key) {
        return apcu_fetch($this->buildKey($key));
    }

    public function getValues(array $keys): array {
        $values = apcu_fetch($this->buildKeys($keys));
        return is_array($values) ? $values : [];
    }

    public function set(string $key, $value, int $duration): bool {
        return apcu_store($this->buildKey($key), $value, $duration);
    }

    public function setValues(array $data, int $duration): array {
        $hashedData = array();
        foreach ($data as $key => $value) {
            $hashedData[$this->buildKey($key)] = $value;
        }
        $result = apcu_store($hashedData, null, $duration);
        return is_array($result) ? array_keys($result) : [];
    }

    public function add(string $key, $value, int $duration): bool {
        return apcu_add($this->buildKey($key), $value, $duration);
    }

    public function addValues(array $data, int $duration): array {
        $hashedData = array();
        foreach ($data as $key => $value) {
            $hashedData[$this->buildKey($key)] = $value;
        }
        $result = apcu_add($hashedData, null, $duration);
        return is_array($result) ? array_keys($result) : [];
    }

    public function delete(string $key): bool {
        return apcu_delete($this->buildKey($key));
    }

    public function flush(): bool {
        return apcu_clear_cache();
    }

}
