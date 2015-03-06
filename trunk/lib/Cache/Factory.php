<?php
namespace FMUP\Cache;

final class Factory
{
    const DRIVER_RAM = 'Ram';

    private function __construct()
    {
    }
    
    /**
     * @param string $driver
     * @param array $params
     * @return CacheInterface
     * @throws Exception
     */
    public static function create($driver = self::DRIVER_RAM, $params = array())
    {
        $class = 'FMUP\\Cache\\Driver\\' . $driver;
        if (!class_exists($class)) {
            throw new Exception('Unable to create ' . $class);
        }
        $instance = new $class($params);
        if (!$instance instanceof CacheInterface) {
            throw new Exception('Unable to create ' . $class);
        }

        return $instance;
    }
}
