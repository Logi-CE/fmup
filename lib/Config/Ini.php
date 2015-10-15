<?php
namespace FMUP\Config;

use FMUP\Config;

class Ini implements ConfigInterface
{
    private $filePath;
    private $environment;
    private $config = array();

    /**
     * @param string $filePath
     * @param string|null $environment
     */
    public function __construct($filePath, $environment = null)
    {
        $this->filePath = $filePath;
        $this->environment = $environment;
    }

    /**
     * @return string
     */
    protected function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @return null|string
     */
    protected function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @return array
     */
    protected function getConfig()
    {
        if (!$this->config) {
            $this->config = parse_ini_file($this->getFilePath(), $this->getEnvironment());
        }
        return $this->config;
    }

    /**
     * @param string|null $key
     * @return mixed|null
     */
    public function get($key = null)
    {
        if ($this->has($key) || is_null($key)) {
            $config = $this->getConfig();
            return is_null($key) ? $config : $config[$key];
        }
        return null;
    }

    /**
     * @param string $paramName
     * @param string|null $value
     * @return $this
     */
    public function set($paramName, $value = null)
    {
        $this->config = $this->getConfig();
        $this->config[$paramName] = $value;
        return $this;
    }

    /**
     * @param string $paramName
     * @return bool
     */
    public function has($paramName)
    {
        $config = $this->getConfig();
        return isset($config[$paramName]);
    }

    /**
     * @param array $paramArray
     * @param bool|false $before
     * @return $this
     */
    public function mergeConfig(array $paramArray = array(), $before = false)
    {
        if ($before) {
            $this->config = $this->getConfig() + $paramArray;
        } else {
            $this->config = $paramArray + $this->getConfig();
        }
        return $this;
    }
}
