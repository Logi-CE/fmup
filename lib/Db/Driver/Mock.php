<?php
namespace FMUP\Db\Driver;

use FMUP\Db\DbInterface;
use FMUP\Db\Exception;

class Mock implements DbInterface
{
    protected $instance = NULL;
    protected $params = array();

    public function __construct($params = array())
    {
        $this->params = $params;
    }

    public function getDriver()
    {
        return $this;
    }


    public function beginTransaction()
    {
        return $this;
    }

    public function rollback()
    {
        return $this;
    }

    public function errorCode()
    {
        return $this;
    }

    public function errorInfo()
    {
        return $this;
    }

    public function commit()
    {
        return $this;
    }

    public function rawExecute($sql)
    {
        return $this;
    }

    public function execute($statement, $values = array())
    {
        return $this;
    }

    public function prepare($sql)
    {
        return $this;
    }

    public function lastInsertId()
    {
        return $this;
    }

    public function fetchRow($statement)
    {
        return $this;
    }

    public function fetchAll($statement)
    {
        return $this;
    }
}
