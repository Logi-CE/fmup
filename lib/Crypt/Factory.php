<?php
namespace FMUP\Crypt;

final class Factory
{
    const DRIVER_MD5 = "Md5";
    const DRIVER_MCRYPT = "MCrypt";

    private function __construct()
    {
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
