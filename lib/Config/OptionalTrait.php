<?php
namespace FMUP\Config;

/**
 * Class OptionalTrait
 * @package FMUP\Config
 */
trait OptionalTrait
{
    private $config;

    /**
     * Define config
     * @param ConfigInterface|null $configInterface
     * @return $this
     */
    public function setConfig(ConfigInterface $configInterface = null)
    {
        $this->config = $configInterface;
        return $this;
    }

    /**
     * Retrieve defined config
     * @return ConfigInterface
     */
    public function getConfig()
    {
        if (!$this->hasConfig()) {
            $this->config = new \FMUP\Config();
        }
        return $this->config;
    }

    /**
     * Check if config exists
     * @return bool
     */
    public function hasConfig()
    {
        return (bool)$this->config;
    }
}
