<?php
namespace FMUP\Cache;

class Factory
{
    const DRIVER_RAM = 'Ram';
    const DRIVER_FILE = 'File';
    const DRIVER_SHM = 'Shm';
    private static $instance;

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * @codeCoverageIgnore
     */
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
     * @param string $driver
     * @param array $params
     * @return CacheInterface
     * @throws Exception
     */
    final public function create($driver = self::DRIVER_RAM, $params = array())
    {
        $class = $this->getClassForName($driver);
        if (!class_exists($class)) {
            throw new Exception('Unable to create ' . $class);
        }
        $instance = new $class($params);
        if (!$instance instanceof CacheInterface) {
            throw new Exception('Unable to create ' . $class);
        }
        return $instance;
    }

    /**
     * Must return full class name for specified driver name
     * @param string $driver
     * @return string
     */
    protected function getClassForName($driver)
    {
        return __NAMESPACE__  . '\Driver\\' . ucfirst($driver);
    }
}
