<?php
namespace FMUP\Config\Ini;

use FMUP\Config\Ini;

/**
 * Class Extended
 * @package FMUP\Config\Ini
 * @uses Zend_Config_Ini
 */
class Extended extends Ini
{
    private $config;

    /**
     * @uses Zend_Config_Ini
     * @return Zend_Config_Ini
     */
    protected function getConfig()
    {
        if (!$this->config) {
            $options = array('allowModifications' => true);
            $this->config = new Zend_Config_Ini($this->getFilePath(), $this->getEnvironment(), $options);
        }
        return $this->config;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get($key)
    {
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
