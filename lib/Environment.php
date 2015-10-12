<?php
namespace FMUP;

class Environment
{
    private static $instance;
    private $config;

    const PROD = 'prod';
    const PREPROD = 'preprod';
    const DEV = 'dev';
    const INTEGRATION = 'integ';

    private function __construct()
    {

    }

    private function __clone()
    {

    }

    /**
     * @return self
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            $class = get_called_class();
            self::$instance = new $class();
        }
        return self::$instance;
    }

    /**
     * @param Config $config
     * @return $this
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasConfig()
    {
        return (bool)$this->config;
    }

    /**
     * @return Config
     * @throws Exception
     */
    public function getConfig()
    {
        if (!$this->config) {
            throw new Exception('Config is not defined');
        }
        return $this->config;
    }

    public function get()
    {
        if (defined('ENVIRONMENT')) {
            return ENVIRONMENT;
        }
        if ($this->getConfig()->has('version')) {
            return $this->getConfig()->get('version');
        }
        throw new Exception('No environment detected');
    }
}
