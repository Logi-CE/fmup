<?php
namespace FMUP\Cache\Driver;

use FMUP\Cache\CacheInterface;
use FMUP\Cache\Exception;

/**
 * Class Redis
 * This driver needs Redis installed on server to work properly
 * @package FMUP\Cache\Driver
 */
class Redis implements CacheInterface
{
    const SETTINGS_REDIS = 'SETTINGS_REDIS';

    const SETTINGS_TTL_IN_SECOND = 'SETTINGS_TTL_IN_SECOND';
    const SETTINGS_CACHE_PREFIX = 'SETTINGS_CACHE_PREFIX';

    private $isAvailable = null;
    private $redisInstance = null;
    private $settings = array();

    /**
     * Check whether redis is available
     * @return bool
     */
    public function isAvailable()
    {
        if (!$this->isAvailable) {
            try {
                $this->getRedisInstance()->ping();
                $this->isAvailable = true;
            } catch (\Exception $e) {
                $this->isAvailable = false;
            }
        }
        return $this->isAvailable;
    }

    /**
     * constructor of redis
     * @param array $settings
     */
    public function __construct($settings = array())
    {
        if (isset($settings[self::SETTINGS_REDIS])) {
            $this->setRedisInstance($settings[self::SETTINGS_REDIS]);
        }
        $this->settings = $settings;
    }

    /**
     * Get the redis instance
     * @return \Predis\Client
     * @throws Exception
     */
    public function getRedisInstance()
    {
        if (!$this->redisInstance) {
            $this->redisInstance = $this->createRedis();
        }
        return $this->redisInstance;
    }

    /**
     * @return \Predis\Client
     */
    private function createRedis()
    {
        return new \Predis\Client($this->settings);
    }

    /**
     * Define a Predis instance
     * @param \Predis\Client $redisInstance
     * @return $this
     */
    public function setRedisInstance(\Predis\Client $redisInstance)
    {
        $this->redisInstance = $redisInstance;
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
        return $this->getRedisInstance()->exists($this->getCacheKey($key));
    }

    /**
     * @param string $key
     * @return mixed
     * @throws Exception
     */
    public function get($key)
    {
        return $this->getRedisInstance()->get($this->getCacheKey($key));
    }

    /**
     * Define a value for a given key in redis
     * @param string $key
     * @param mixed $value
     * @return $this
     * @throws Exception
     */
    public function set($key, $value)
    {
        $ttl = (int)$this->getSetting(self::SETTINGS_TTL_IN_SECOND);
        $key = $this->getCacheKey($key);
        $redis = $this->getRedisInstance();
        $return = $redis->set($key, $value);
        if ($ttl) {
            $redis->expireAt($key, time() + $ttl);
            $redis->ttl($key);
        }
        if (!$return) {
            throw new Exception('Error while inserting value in redis!');
        }
        return $this;
    }

    /**
     * Delete a value in redis
     * @param string $key
     * @return $this
     * @throws Exception
     */
    public function remove($key)
    {
        $key = $this->getCacheKey($key);
        if (!$this->getRedisInstance()->delete($key)) {
            throw new Exception('Error while deleting key in redis');
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
