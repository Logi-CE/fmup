<?php
namespace FMUP;

class Environment
{
    use Config\OptionalTrait;
    private static $instance;

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
    final public static function getInstance()
    {
        if (!self::$instance) {
            $class = get_called_class();
            self::$instance = new $class();
        }
        return self::$instance;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function get()
    {
        if (defined('ENVIRONMENT')) {
            return ENVIRONMENT;
        }
        if ($this->getConfig()->has('version')) {
            return (string)$this->getConfig()->get('version');
        }
        throw new Exception('No environment detected');
    }
}
