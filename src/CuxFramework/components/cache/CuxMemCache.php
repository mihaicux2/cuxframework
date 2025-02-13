<?php

/**
 * CuxMemCache class file
 * 
 * @package Components
 * @subpackage Cache
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\components\cache;

use CuxFramework\utils\CuxBase;
use CuxFramework\utils\Cux;

/**
 * Cache class that uses Memcache to store data
 * 
 * Usage example <b>(direct initialization)</b>:
 * 
 * <code>
 * <?php<br />
 * use CuxFramework\components\cache\CuxMemCache;<br />
 * $cache = new CuxFileCache();<br />
 * $cache->config(array(<br />
 *      "lifeTime" => 1800, // 30 minutes cache lifeTime<br />
 *      "servers" => array(<br />
 *          array(<br />
 *               "host" => "localhost",<br />
 *               "port" => 11211
 *          )<br />
 *      )<br />
 * ));<br />
 * $data = $cache->get("cachedData");<br />
 * if (!$data){<br />
 *     $data = "stored data";<br />
 *     $cache->set("cachedData", $data, 600); // store the data for 10 minutes<br />
 * }
 * ?>
 * </code>
 * 
 * 
 * Usage example <b>(framework usage)</b>:
 * 
 * <i>config.php</i>
 * 
 * <code>
 * <?php<br />
 * "components" => array(<br />
 *     ... <br />
 *     "cache" => array( <br />
            'class' => 'CuxFramework\components\cache\CuxMemCache', <br />
            'params' => array( <br />
                "lifeTime" => 1800 <br />
            ) <br />
        ) <br />
 * ) <br />
 * ?>
 * </code>
 * 
 * <i>test.php</i>
 * 
 * <code>
 * <?php<br />
 * use CuxFramework\components\utils\Cux;<br />
 * 
 * $cache = Cux::getInstance()->cache();<br />
 * $data = $cache->get("cachedData");<br />
 * if (!$data){<br />
 *     $data = "stored data";<br />
 *     $cache->set("cachedData", $data, 600); // store the data for 10 minutes<br />
 * }
 * ?>
 * </code>
 */
class CuxMemCache extends CuxCache {

    /**
     * Instance of the Memcache object
     * @var Memcache 
     */
    private $_memcache;
    
    /**
     * The list of Memcache servers, defined with "host" and "port" properties
     * @var array
     */
    protected $servers = array();
    
    /**
     * Setup object instance properties
     * @param array $config
     * @throws \Exception
     */
    public function config(array $config) {
        parent::config($config);
        $extension = "memcache";
        if (!extension_loaded($extension)) {
            throw new \Exception(Cux::translate("core.errors", "Extension not found: {extension}", array("{extension}" => $extension), "Error shown on missing extension"), 503);
        }
        $this->_memcache = new \Memcache();
        if (is_array($this->servers) && !empty($this->servers)) {
            foreach ($this->servers as $server) {
                $this->_memcache->addServer($server["host"], $server["port"]);
            }
        }
    }

    /**
     * Checks wether the cache contains a specific key
     * @param string $key a unique key identifying the cached value
     * @return boolean true if the cache contains the given key
     */
    public function exists(string $key): bool {
        return true;
    }

    /**
     * Retrieves a value from cache with a specified key.
     * @param string $key a unique key identifying the cached value
     * @return string|boolean the value stored in cache, false if the value is not in the cache or expired.
     */
    public function get(string $key) {
        return $this->_memcache->get($this->buildKey($key));
    }

    /**
     * Retrieves multiple values from cache with the specified keys.
     * @param array $keys a list of keys identifying the cached values
     * @return array a list of cached values indexed by the keys
     */
    public function getValues(array $keys): array {
        $values = $this->_memcache->fetchAll($this->buildKeys($keys));
        return is_array($values) ? $values : [];
    }

    /**
     * Stores a value identified by a key in cache.     *
     * @param string $key the key identifying the value to be cached
     * @param string $value the value to be cached
     * @param integer $duration the number of seconds in which the cached value will expire. 0 means never expire.
     * @return boolean true if the value is successfully stored into cache, false otherwise
     */
    public function set(string $key, $value, int $duration = null): bool {
        return $this->_memcache->set($this->buildKey($key), $value, MEMCACHE_COMPRESSED, $duration);
    }

    /**
     * Stores multiple key-value pairs in cache.
     * @param array $data array where key corresponds to cache key while value
     * @param integer $duration the number of seconds in which the cached values will expire. 0 means never expire.
     * @return array list of failed keys
     */
    public function setValues(array $data, int $duration = null): array {
        $hashedData = array();
        foreach ($data as $key => $value) {
            $hashedData[$this->buildKey($key)] = $value;
        }
        $result = $this->_memcache->setMulti($hashedData, $duration);
        return $result ? array_keys($data) : [];
    }

    /**
     * Stores a value identified by a key into cache if the cache does not contain this key.
     * @param string $key the key identifying the value to be cached
     * @param string $value the value to be cached
     * @param integer $duration the number of seconds in which the cached value will expire. 0 means never expire.
     * @return boolean true if the value is successfully stored into cache, false otherwise
     */
    public function add(string $key, $value, int $duration): bool {
        return $this->_memcache->add($this->buildKey($key), $value, MEMCACHE_COMPRESSED, $duration);
    }

    /**
     * Adds multiple key-value pairs to cache.
     * @param array $data array where key corresponds to cache key while value is the value stored
     * @param integer $duration the number of seconds in which the cached values will expire. 0 means never expire.
     * @return array list of failed keys
     */
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

    /**
     * Deletes a value with the specified key from cache
     * @param string $key the key of the value to be deleted
     * @return boolean if no error happens during deletion
     */
    public function delete(string $key): bool {
        return $this->_memcache->delete($this->buildKey($key));
    }

    /**
     * Deletes all values from cache.
     * @return boolean whether the flush operation was successful.
     */
    public function flush(): bool {
        return $this->_memcache->flush();
    }

} 