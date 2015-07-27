<?php
namespace FMUP;

/**
 * Class Config
 * @package FMUP
 */
class Config
{
    /**
     * @var array
     */
    private $params = array();

    /**
     * Retrieve defined param
     * @param string $paramName
     * @return array|null
     */
    public function get($paramName = NULL)
    {
        if (true == is_null($paramName)) {
            return $this->params;
        }
        return $this->has($paramName) ? $this->params[$paramName] : NULL;
    }

    /**
     * Define a param
     * @param string $paramName
     * @param mixed $value default NULL
     * @return $this
     */
    public function set($paramName, $value = NULL)
    {
        $this->params[$paramName] = $value;
        return $this;
    }

    /**
     * Merge an array with current config
     * @param array $paramArray
     * @param bool $before Merge before (default : false)
     * @return $this
     */
    public function mergeConfig($paramArray = array(), $before = false)
    {
        if ($before) {
            $this->params = array_merge($paramArray, $this->params);
        } else {
            $this->params = array_merge($this->params, $paramArray);
        }
        return $this;
    }

    /**
     * Is a param defined
     * @param string $paramName
     * @return bool
     */
    public function has($paramName)
    {
        return isset($this->params[$paramName]);
    }
}
