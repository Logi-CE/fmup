<?php
namespace FMUP\Helper;

/**
 * Class Db
 * @package FMUP\Helper
 */
abstract class Db
{
    const DEFAULT_NAME = 'DEFAULT_NAME';
    /**
     * @var array \FMUP\Db
     */
    protected static $instances = array();

    /**
     * @return \FMUP\Db
     */
    public static function getInstance($name = self::DEFAULT_NAME)
    {
        if (is_null($name)) {
            throw new \InvalidArgumentException('Name must be set');
        }
        $name = (string)$name;
        if (is_null(self::$instances[$name])) {
            if ($name == self::DEFAULT_NAME) {
                $params = \Config::parametresConnexionDb();
            } else {
                $dbSettings = \Config::paramsVariables('db');
                if (isset($dbSettings[$name])) {
                    $params = $dbSettings[$name];
                } else {
                    throw new \OutOfRangeException('Trying to access a database name ' . $name . ' that not exists');
                }
            }
            self::$instances[$name] = new \FMUP\Db($params);
        }

        return self::$instances[$name];
    }

    /**
     * Singleton - private construct
     */
    private function __construct()
    {

    }
}
