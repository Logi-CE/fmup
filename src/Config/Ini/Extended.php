<?php
namespace FMUP\Config\Ini;

use FMUP\Config\Exception;
use FMUP\Config\Ini;

/**
 * Class Extended
 * Config Ini Extended applies old Zend_Config_Ini inheritance feature
 * (in the same component since Zend_Config_Ini is not compliant with composer)
 * @package FMUP\Config\Ini
 * @uses Zend_Config_Ini (version 1.12.16)
 */
class Extended extends Ini
{
    private $config;

    /**
     * @uses FMUP\Config
     * @uses Extended\ZendConfig\Ini
     * @return Extended\ZendConfig\Ini
     */
    public function getConfig()
    {
        if (!$this->config) {
            $this->config = new Extended\ZendConfig\Ini($this->getFilePath(), $this->getSection(), true);
        }
        return $this->config;
    }

    /**
     * @param Extended\ZendConfig\Ini $config
     * @return $this
     */
    public function setConfig(Extended\ZendConfig\Ini $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get($key = null)
    {
        if (is_null($key)) {
            return (array)$this->getConfig();
        }
        return $this->has($key) ? $this->getConfig()->$key : null;
    }

    /**
     * @param string $paramName
     * @return bool
     */
    public function has($paramName)
    {
        return isset($this->getConfig()->$paramName);
    }

    /**
     * @param string $paramName
     * @param mixed|null $value
     * @return $this
     */
    public function set($paramName, $value = null)
    {
        $this->getConfig()->$paramName = $value;
        return $this;
    }
}
