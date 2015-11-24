<?php
namespace FMUP;

/**
 * Class Sapi - This class test if context is from web (apache) or CLI
 * @package FMUP
 */
class Sapi
{
    private static $instance;

    const CLI = 'cli';
    const CGI = 'cgi';

    /**
     * @return $this
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            $class = get_called_class();
            self::$instance = new $class;
        }
        return self::$instance;
    }

    /**
     * Private construct - Singleton
     */
    private function __construct()
    {

    }

    private function __clone()
    {

    }

    /**
     * Get the Sapi
     * @return string
     */
    public function get()
    {
        return $this->isCli() ? self::CLI : self::CGI;
    }

    /**
     * Get Raw Sapi
     * @return string
     */
    public function getRaw()
    {
        return php_sapi_name();
    }

    /**
     * Test if current sapi is the one requested
     * @param string $sapi
     * @return bool
     */
    public function is($sapi)
    {
        return $this->getRaw() == $sapi;
    }

    /**
     * Check whether we are in cli
     * @return bool
     */
    protected function isCli()
    {
        return strtolower(substr($this->getRaw(), 0, 3)) == self::CLI;
    }
}
