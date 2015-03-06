<?php
namespace FMUP\Cache;

interface CacheInterface
{
    /**
     * Construct instance must allow array of parameters
     * @param array $params
     */
    public function __construct($params);

    public function set($key, $value);
    public function get($key);
    public function has($key);
    public function remove($key);
}
