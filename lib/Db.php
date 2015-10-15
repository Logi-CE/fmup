<?php
namespace FMUP;

use FMUP\Db\Factory;

/**
 * Class Db
 * @package FMUP
 */
class Db
{
    protected $driver = Factory::DRIVER_PDO;
    protected $params = array();
    private $driverInstance = null;

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

        $this->driverInstance = Factory::getInstance()->create($this->driver, $this->params);

        return $this->driverInstance;
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
     */
    public function fetchAll($sql, array $params = array())
    {
        $statement = $this->getDriver()->prepare($sql);
        $this->getDriver()->execute($statement, $params);
        /**
         * @todo tune with DB cursor if available // move this to driver
         */
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
}
