<?php
namespace FMUP\Db;

class Factory
{
    const DRIVER_PDO = 'Pdo';
    const DRIVER_PDO_ODBC = 'Pdo\\Odbc';
    const DRIVER_PDO_SQLSRV = 'Pdo\\SqlSrv';
    const DRIVER_PDO_SQLITE = 'Pdo\\Sqlite';
    const DRIVER_MOCK = 'Mock';

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
