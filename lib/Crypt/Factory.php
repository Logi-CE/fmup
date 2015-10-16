<?php
namespace FMUP\Crypt;

class Factory
{
    const DRIVER_MD5 = "Md5";
    const DRIVER_MCRYPT = "MCrypt";

    private static $instance;

    protected function __construct()
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
     *
     * @param string $driver
     * @return \FMUP\Crypt\CryptInterface
     * @throws Exception
     */
    public static function create($driver = self::DRIVER_MD5)
    {
        $class = 'FMUP\\Crypt\\Driver\\' . $driver;
        if (!class_exists($class)) {
            throw new Exception('Unable to create ' . $class);
        }
        $instance = new $class();
        if (!$instance instanceof CryptInterface) {
            throw new Exception('Unable to create ' . $class);
        }
        return $instance;
    }
}
