<?php
namespace FMUP;

use FMUP\Cache\Factory;

/**
 * Get the Cache instance
 * @package FMUP
 * @example
 */
class Cache
{
    /**
     * Cache instance
     * @var Cache
     */
    protected static $instance = array();

    protected $driver = Factory::DRIVER_RAM;
    protected $cacheInstance = null;
    protected $params = array();

    /**
     * Multiton - private construct
     */
    private function __construct()
    {
    }

    /**
     * @param string $instanceKey
     * @return $this
     */
    public static function getInstance($instanceKey)
    {
        if (!isset(self::$instance[$instanceKey])) {
            $class = get_called_class();
            /* @var $instance $this */
            $instance = new $class;
            self::$instance[$instanceKey] = $instance->setDriver($instanceKey);
        }
        return self::$instance[$instanceKey];
    }

    /**
     * @return Cache\CacheInterface|null
     */
    public function getDriver()
    {
        if (!is_null($this->cacheInstance)) {
            return $this->cacheInstance;
        }

        $this->cacheInstance = Factory::create($this->driver, $this->params);

        return $this->cacheInstance;
    }

    /**
     * set cacheInstance
     * @param \FMUP\Cache\CacheInterface $instance
     * @return \FMUP\Cache
     */
    public function setCacheInstance(Cache\CacheInterface $instance)
    {
        $this->cacheInstance = $instance;
        return $this;
    }

    /**
     * set driver
     * @param string $driver
     * @return \FMUP\Cache
     */
    public function setDriver($driver)
    {
        $this->driver = $driver;
        return $this;
    }

    /**
     * set params for construct \FMUP\Cache\CacheInterface
     * @param array $params
     * @return \FMUP\Cache
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * set a param in cache
     * @param string $key
     * @param mixed $value
     * @return \FMUP\Cache
     */
    public function set($key, $value)
    {
        $this->getDriver()->set((string)$key, $value);
        return $this;
    }

    /**
     * return value of a param
     * @param string $key
     * @return mixed|null
     */
    public function get($key)
    {
        return $this->getDriver()->get((string)$key);
    }

    /**
     * check param is set
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return $this->getDriver()->has((string)$key);
    }

    /**
     * remove param
     * @param string $key
     * @return \FMUP\Cache
     */
    public function remove($key)
    {
        $this->getDriver()->remove($key);
        return $this;
    }
}
