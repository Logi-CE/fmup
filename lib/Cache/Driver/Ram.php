<?php
namespace FMUP\Cache\Driver;

use FMUP\Cache\CacheInterface;

class Ram implements CacheInterface
{
    protected $params = array();

    /**
     * constructor of Ram
     * @param array $params
     */
    public function __construct($params = array())
    {
        $this->params = $params;
    }

    /**
     * set a param
     * @param string $key
     * @param mixed $value
     * @return \FMUP\Cache\Driver\Ram
     */
    public function set($key, $value)
    {
        $this->params[$key] = $value;
        return $this;
    }

    /**
     * get a param
     * @param string $key
     * @return mixed|null
     */
    public function get($key)
    {
        return isset($this->params[$key]) ? $this->params[$key] : null;
    }

    /**
     * has a param
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->params[$key]) ? true : false;
    }

    /**
     * remove a param in object
     * @param string $key
     * @return \FMUP\Cache\Driver\Ram
     */
    public function remove($key)
    {
        unset($this->params[$key]);
        return $this;
    }
}