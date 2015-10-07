<?php
namespace FMUP\Helper;

/**
 * Class Db
 * @package FMUP\Helper
 */
abstract class Db
{
    const DEFAULT_NAME = 'DEFAULT_NAME';
    protected static $instances = array();
    protected static $config = null;

    /**
     * @param string $name
     * @return \FMUP\Db
     * @throws \InvalidArgumentException
     * @throws \OutOfRangeException
     */
    public static function getInstance($name = self::DEFAULT_NAME)
    {
        if (is_null($name)) {
            throw new \InvalidArgumentException('Name must be set');
        }
        $name = (string)$name;
        if (!isset(self::$instances[$name])) {
            if ($name == self::DEFAULT_NAME) {
                $params = self::getConfig()->get('parametres_connexion_db');
            } else {
                $dbSettings = self::getConfig()->get('db');
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
     * Multiton - private construct
     */
    private function __construct()
    {

    }

    /**
     * @param \FMUP\Config $config
     */
    public static function setConfig(\FMUP\Config $config)
    {
        self::$config = $config;
    }

    /**
     * @return \FMUP\Config
     * @throws \LogicException
     */
    public static function getConfig()
    {
        if (!self::$config) {
            throw new \LogicException('Config is not defined and required!');
        }
        return self::$config;
    }
}
