<?php
namespace FMUP\Helper;

use FMUP\Config\ConfigInterface;
use FMUP\Logger;

/**
 * Class Db
 * @package FMUP\Helper
 */
class Db implements Logger\LoggerInterface
{
    use Logger\LoggerTrait;

    const DEFAULT_NAME = 'DEFAULT_NAME';
    private static $instance = null;
    private $config = null;
    private $instances = array();

    private function __construct()
    {

    }

    private function __clone()
    {

    }

    /**
     * @param string $name
     * @return \FMUP\Db
     * @throws \InvalidArgumentException
     * @throws \OutOfRangeException
     */
    public function get($name = self::DEFAULT_NAME)
    {
        if (is_null($name)) {
            throw new \InvalidArgumentException('Name must be set');
        }
        $name = (string)$name;
        if (!isset($this->instances[$name])) {
            if ($name == self::DEFAULT_NAME) {
                $params = $this->getConfig()->get('parametres_connexion_db');
            } else {
                $dbSettings = $this->getConfig()->get('db');
                if (isset($dbSettings[$name])) {
                    $params = $dbSettings[$name];
                } else {
                    throw new \OutOfRangeException('Trying to access a database name ' . $name . ' that not exists');
                }
            }
            $instance = new \FMUP\Db($params);
            if ($this->hasLogger()) {
                $instance->setLogger($this->getLogger());
            }
            $this->instances[$name] = $instance;
        }

        return $this->instances[$name];
    }

    /**
     * @return $this
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
     * @param ConfigInterface $config
     * @return $this
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return ConfigInterface
     * @throws \LogicException
     */
    public function getConfig()
    {
        if (!$this->config) {
            throw new \LogicException('Config is not defined and required!');
        }
        return $this->config;
    }
}
