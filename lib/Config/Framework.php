<?php
namespace FMUP\Config;

class Framework extends \FMUP\Config
{
    private static $instance;

    public static function getInstance()
    {
        if (!self::$instance) {
            $class = get_called_class();
            self::$instance = new $class;
        }
        return self::$instance;
    }

    protected function __construct()
    {

    }

    private function __clone()
    {

    }


}