<?php
namespace FMUP\Config;

interface ConfigInterface
{
    /**
     * Retrieve defined param
     * @param string $paramName
     * @return array|string|null
     */
    public function get($paramName = null);

    /**
     * Define a param
     * @param string $paramName
     * @param mixed $value default null
     * @return $this
     */
    public function set($paramName, $value = null);

    /**
     * Merge an array with current config
     * @param array $paramArray
     * @param bool $before Merge before (default : false)
     * @return $this
     */
    public function mergeConfig(array $paramArray = array(), $before = false);

    /**
     * Is a param defined
     * @param string $paramName
     * @return bool
     */
    public function has($paramName);
}
