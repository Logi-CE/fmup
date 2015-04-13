<?php
namespace FMUP\Cache;

interface CacheInterface
{
    /**
     * Construct instance must allow array of parameters
     * @param array $params
     */
    public function __construct($params);

    /**
     * Define a value in cache in its cache key
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function set($key, $value);

    /**
     * Retrieve defined value in cache
     * @param string $key
     * @return mixed
     */
    public function get($key);

    /**
     * Check is cache key is defined in cache
     * @param string $key
     * @return bool
     */
    public function has($key);

    /**
     * Clear cache for a specified key
     * @param string $key
     * @return $this
     */
    public function remove($key);
}
