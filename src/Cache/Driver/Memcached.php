<?php
namespace FMUP\Cache\Driver;

use FMUP\Cache\CacheInterface;
use FMUP\Cache\Exception;

/**
 * Class Memcached
 * This driver needs MEMCACHED installed on server to work properly
 * @package FMUP\Cache\Driver
 */
class Memcached implements CacheInterface
{
    const SETTINGS_MEMCACHED = 'SETTINGS_MEMCACHED';
    /**
     * @see http://php.net/manual/fr/memcached.expiration.php
     */
    const SETTINGS_TTL_IN_SECOND = 'SETTINGS_TTL_IN_SECOND';
    const SETTINGS_CACHE_PREFIX = 'SETTINGS_CACHE_PREFIX';

    private $isAvailable = null;
    private $memcachedInstance = null;
    private $settings = array();

    /**
     * Check whether memcached is available
     * @return bool
     */
    public function isAvailable()
    {
        if (is_null($this->isAvailable)) {
            $this->isAvailable = class_exists('\Memcached');
        }
        return $this->isAvailable;
    }

    /**
     * constructor of Memcached
     * @param array $settings
     */
    public function __construct($settings = array())
    {
        if (isset($settings[self::SETTINGS_MEMCACHED])) {
            $this->setMemcachedInstance($settings[self::SETTINGS_MEMCACHED]);
        }
        $this->settings = $settings;
    }

    /**
     * Get the memcached instance
     * @return \Memcached
     * @throws Exception
     */
    public function getMemcachedInstance()
    {
        if (!$this->memcachedInstance) {
            if (!$this->isAvailable()) {
                throw new Exception('Memcached is not available');
            }
            $this->memcachedInstance = $this->createMemcached();
        }
        return $this->memcachedInstance;
    }

    /**
     * @return \Memcached
     * @codeCoverageIgnore
     */
    protected function createMemcached()
    {
        return new \Memcached();
    }

    /**
     * Define a memcached instance
     * @param \Memcached $memcachedInstance
     * @return $this
     */
    public function setMemcachedInstance(\Memcached $memcachedInstance)
    {
        $this->memcachedInstance = $memcachedInstance;
        return $this;
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getCacheKey($key)
    {
        $prefix = (string)$this->getSetting(self::SETTINGS_CACHE_PREFIX);
        return $prefix . $key;
    }


    /**
     * Check whether a key exists
     * @param string $key
     * @return bool
     * @throws Exception
     */
    public function has($key)
    {
        if (!$this->isAvailable()) {
            throw new Exception('Memcached is not available');
        }
        $key = $this->getCacheKey($key);
        $keys = array_flip($this->getMemcachedInstance()->getAllKeys());
        return isset($keys[$key]);
    }

    /**
     * @param string $key
     * @return mixed
     * @throws Exception
     */
    public function get($key)
    {
        if (!$this->isAvailable()) {
            throw new Exception('Memcached is not available');
        }
        $key = $this->getCacheKey($key);
        return $this->getMemcachedInstance()->get($key);
    }

    /**
     * Define a
     * @param string $key
     * @param mixed $value
     * @return $this
     * @throws Exception
     */
    public function set($key, $value)
    {
        if (!$this->isAvailable()) {
            throw new Exception('Memcached is not available');
        }
        $ttl = (int)$this->getSetting(self::SETTINGS_TTL_IN_SECOND);
        $key = $this->getCacheKey($key);
        if (!$this->getMemcachedInstance()->set($key, $value, $ttl)) {
            throw new Exception(
                'Error while inserting value in memcached : ' . $this->getMemcachedInstance()->getResultMessage(),
                $this->getMemcachedInstance()->getResultCode()
            );
        }
        return $this;
    }

    /**
     * Delete a value in memcache
     * @param string $key
     * @return $this
     * @throws Exception
     */
    public function remove($key)
    {
        if (!$this->isAvailable()) {
            throw new Exception('Memcached is not available');
        }
        $key = $this->getCacheKey($key);
        if (!$this->getMemcachedInstance()->delete($key)) {
            throw new Exception('Error while deleting key in memcached');
        }
        return $this;
    }

    /**
     * Define a setting
     * @param string $setting
     * @param mixed $value
     * @return $this
     */
    public function setSetting($setting, $value)
    {
        $this->settings[(string)$setting] = $value;
        return $this;
    }

    /**
     * Retrieve a defined setting
     * @param string $setting
     * @return mixed|null
     */
    public function getSetting($setting)
    {
        $setting = (string) $setting;
        return isset($this->settings[$setting]) ? $this->settings[$setting] : null;
    }
}
