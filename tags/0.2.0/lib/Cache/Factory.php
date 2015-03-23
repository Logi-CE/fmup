<?php
namespace FMUP\Cache;

final class Factory
{
    const DRIVER_RAM = 'Ram';
    const DRIVER_FILE = 'File';

    private function __construct()
    {
    }
    
    /**
     * @param string $driver
     * @param array $params
     * @return CacheInterface
     * @throws \FMUP\Exception
     */
    public static function create($driver = self::DRIVER_RAM, $params = array())
    {
        $class = 'FMUP\\Cache\\Driver\\' . $driver;
        if (!class_exists($class)) {
            throw new \FMUP\Exception('Unable to create ' . $class);
        }
        $instance = new $class($params);
        if (!$instance instanceof CacheInterface) {
            throw new \FMUP\Exception('Unable to create ' . $class);
        }

        return $instance;
    }
}
