<?php
namespace FMUP\Db;

abstract class Factory
{
    const DRIVER_PDO = 'Pdo';

    /**
     * @param string $driver
     * @param array $params
     * @return DbInterface
     * @throws Exception
     */
    public static function create($driver = self::DRIVER_PDO, $params = array())
    {
        $class = 'FMUP\\Db\\Driver\\' . $driver;
        if (!class_exists($class)) {
            throw new Exception('Unable to create ' . $class);
        }
        $instance = new $class($params);
        if (!$instance instanceof DbInterface) {
            throw new Exception('Unable to create ' . $class);
        }

        return $instance;
    }
}
