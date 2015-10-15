<?php
namespace FMUP\Cache;

class Factory
{
    const DRIVER_RAM = 'Ram';
    const DRIVER_FILE = 'File';
    const DRIVER_SHM = 'Shm';
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
     * @param string $driver
     * @param array $params
     * @return CacheInterface
     * @throws Exception
     */
    public function create($driver = self::DRIVER_RAM, $params = array())
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
