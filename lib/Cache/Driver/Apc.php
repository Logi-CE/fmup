<?php
namespace FMUP\Cache\Driver;

use FMUP\Cache\CacheInterface;
use FMUP\Cache\Exception;

/**
 * Class Apc
 * This driver needs APC installed on server to work properly
 * @package FMUP\Cache\Driver
 */
class Apc implements CacheInterface
{
    /**
     * @var array
     */
    private $settings = array();
    private $isAvailable = null;

    const SETTING_CACHE_TYPE = 'SETTING_CACHE_TYPE';
    const SETTING_CACHE_TTL = 'SETTING_CACHE_TTL';
    const CACHE_TTL_DEFAULT = 0;

    const CACHE_TYPE_USER = 'user';
    const CACHE_TYPE_FILE_HITS = 'filehits';
    const CACHE_TYPE_OP_CODE = 'opcode';

    /**
     * constructor of File
     * @param array $settings
     */
    public function __construct($settings = array())
    {
        $this->setSettings($settings);
    }

    /**
     * Can define settings of the component
     * @param array $settings
     * @return $this
     */
    public function setSettings($settings = array())
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * Get defined value for specified cache key
     * @param string $key
     * @return mixed
     * @throws Exception
     */
    public function get($key)
    {
        if (!$this->isAvailable()) {
            throw new Exception('APC is not available');
        }
        return apc_fetch($key);
    }

    /**
     * Check whether key exists or not
     * @param string $key
     * @return bool
     * @throws Exception
     */
    public function has($key)
    {
        if (!$this->isAvailable()) {
            throw new Exception('APC is not available');
        }
        return (bool)apc_exists($key);
    }

    /**
     * Delete a specified key in cache
     * @param string $key
     * @return $this
     * @throws Exception
     */
    public function remove($key)
    {
        if (!$this->isAvailable()) {
            throw new Exception('APC is not available');
        }
        if ($this->getCacheType() == self::CACHE_TYPE_OP_CODE) {
            $success = apc_delete_file($key);
        } else {
            $success = apc_delete($key);
        }
        if (!$success) {
            throw new Exception('Unable to delete key from cache APC');
        }
        return $this;
    }

    /**
     * Define a value in cache for a specified name
     * @param string $key
     * @param mixed $value
     * @return $this
     * @throws Exception
     */
    public function set($key, $value)
    {
        if (!$this->isAvailable()) {
            throw new Exception('APC is not available');
        }
        if (!apc_store($key, $value)) {
            throw new Exception('Unable to set key into cache APC');
        }
        return $this;
    }

    /**
     * Clear specified cache type
     * @return bool
     * @throws Exception
     */
    public function clear()
    {
        if (!$this->isAvailable()) {
            throw new Exception('APC is not available');
        }
        return apc_clear_cache($this->getCacheType());
    }

    /**
     * Get information for specified cache type
     * @param bool $limited (default false) If limited is TRUE, the return value will exclude the individual list
     *                                      of cache entries.
     *                                      This is useful when trying to optimize calls for statistics gathering.
     * @return array|bool
     * @throws Exception
     */
    public function info($limited = false)
    {
        if (!$this->isAvailable()) {
            throw new Exception('APC is not available');
        }
        return apc_cache_info($this->getCacheType(), (bool)$limited);
    }

    /**
     * Define a setting
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setSetting($name, $value)
    {
        $this->settings[$name] = $value;
        return $this;
    }

    /**
     * Retrieve a defined setting
     * @param string $name
     * @return mixed
     */
    public function getSetting($name)
    {
        return isset($this->settings[$name]) ? $this->settings[$name] : null;
    }

    /**
     * Get defined cache type
     * @return string
     */
    private function getCacheType()
    {
        $type = $this->getSetting(self::SETTING_CACHE_TYPE);
        if (!$type) {
            return self::CACHE_TYPE_OP_CODE;
        }
        return $type;
    }

    /**
     * Check whether apc is available
     * @return bool
     */
    public function isAvailable()
    {
        if (is_null($this->isAvailable)) {
            $this->isAvailable = function_exists('apc_clear_cache');
        }
        return $this->isAvailable;
    }
}
