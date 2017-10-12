<?php
namespace FMUP;

use FMUP\Db\Factory;
use FMUP\Logger;

/**
 * Class Db
 * @package FMUP
 */
class Db implements Logger\LoggerInterface
{
    use Logger\LoggerTrait;

    protected $driver = Factory::DRIVER_PDO;
    protected $params = array();
    private $driverInstance = null;
    /**
     * @var Factory
     */
    private $factory;

    /**
     * @param array $params
     */
    public function __construct(array $params = array())
    {
        $this->driver = isset($params['db_driver']) ? $params['db_driver'] : Factory::DRIVER_PDO;
        $this->params = $params;
    }

    /**
     * @return Db\DbInterface|null
     * @throws Db\Exception
     */
    public function getDriver()
    {
        if (!is_null($this->driverInstance)) {
            return $this->driverInstance;
        }

        $driverInstance = $this->getFactory()->create($this->driver, $this->params);
        if ($driverInstance instanceof Logger\LoggerInterface && true === $this->hasLogger()) {
            $driverInstance->setLogger($this->getLogger());
        }

        $this->driverInstance = $driverInstance;
        return $this->driverInstance;
    }

    /**
     * @return Factory
     */
    public function getFactory()
    {
        if (!$this->factory) {
            $this->factory = Factory::getInstance();
        }
        return $this->factory;
    }

    /**
     * @param Factory $factory
     * @return $this
     */
    public function setFactory(Factory $factory)
    {
        $this->factory = $factory;
        return $this;
    }

    /**
     * @param string $sql
     * @param array $params
     * @return bool
     */
    public function query($sql, array $params = array())
    {
        $statement = $this->getDriver()->prepare($sql);

        return $this->getDriver()->execute($statement, $params);
    }

    /**
     * @param string $sql
     * @param array $params
     * @return mixed
     * @throws Db\Exception
     * @deprecated use self::getIterator() instead
     */
    public function fetchAll($sql, array $params = array())
    {
        $statement = $this->getDriver()->prepare($sql);
        $this->getDriver()->execute($statement, $params);
        $arrayResult = $this->getDriver()->fetchAll($statement);
        return empty($arrayResult) ? array() : new \ArrayIterator($arrayResult);
    }

    /**
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function fetchRow($sql, array $params = array())
    {
        $statement = $this->getDriver()->prepare($sql);
        $this->getDriver()->execute($statement, $params);

        return $this->getDriver()->fetchRow($statement);
    }

    /**
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->getDriver()->beginTransaction();
    }

    /**
     * @return bool
     */
    public function commit()
    {
        return $this->getDriver()->commit();
    }

    /**
     * @return bool
     */
    public function rollback()
    {
        return $this->getDriver()->rollback();
    }

    /**
     * @param string $name
     * @return string
     */
    public function lastInsertId($name = null)
    {
        return $this->getDriver()->lastInsertId($name);
    }

    /**
     * Force reconnection
     * @return Db\DbInterface
     */
    public function forceReconnect()
    {
        return $this->getDriver()->forceReconnect();
    }

    /**
     * Retrieve an iterator instead of array for data rows
     * @param string $sql
     * @param array $params
     * @return Db\FetchIterator
     */
    public function getIterator($sql, array $params = array())
    {
        return new Db\FetchIterator($this->getDriver()->prepare($sql), $this->getDriver(), $params);
    }
}
