<?php

namespace FMUP\Db\Driver;

use FMUP\Db\Exception;
use FMUP\Logger;

class Pdo extends PdoConfiguration
{
    const CHARSET_UTF8 = 'utf8';

    /**
     * Charset to use
     * @return string
     */
    protected function getCharset()
    {
        $charset = (string)$this->getSettings('charset');
        return $charset ?: self::CHARSET_UTF8;
    }

    /**
     * @param \Pdo $instance
     * @return $this
     */
    protected function defaultConfiguration(\Pdo $instance)
    {
        $charset = $this->getCharset();
        $instance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $instance->setAttribute(\PDO::ATTR_TIMEOUT, 10.0);
        $instance->exec('SET NAMES ' . $charset);
        $instance->exec('SET CHARACTER SET ' . $charset);
        return $this;
    }

    /**
     * Fetch all rows for a given statement
     * @param object $statement
     * @return array
     * @throws Exception
     */
    public function fetchAll($statement)
    {
        if (!$statement instanceof \PDOStatement) {
            $this->log(Logger::ERROR, 'Statement not in right format', array('statement' => $statement));
            throw new Exception('Statement not in right format');
        }

        try {
            return $statement->fetchAll($this->getFetchMode());
        } catch (\PDOException $e) {
            $this->log(Logger::ERROR, $e->getMessage(), array('error' => $e));
            throw new Exception($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Begin a transaction
     * @return bool
     * @throws Exception
     */
    public function beginTransaction()
    {
        try {
            if ($this->getDriver()->inTransaction()) {
                $this->log(Logger::CRITICAL, 'Transaction already opened', $this->getSettings());
                throw new Exception('Transaction already opened');
            }
            $this->log(Logger::DEBUG, 'Transaction start');
            return $this->getDriver()->beginTransaction();
        } catch (\PDOException $e) {
            $this->log(Logger::ERROR, $e->getMessage(), array('settings' => $this->getSettings()));
            throw new Exception($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function rollback()
    {
        try {
            $this->log(Logger::DEBUG, 'Transaction rollback');
            return $this->getDriver()->rollBack();
        } catch (\PDOException $e) {
            $this->log(Logger::ERROR, $e->getMessage(), array('error' => $e));
            throw new Exception($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function errorCode()
    {
        try {
            return $this->getDriver()->errorCode();
        } catch (\PDOException $e) {
            $this->log(Logger::ERROR, $e->getMessage(), array('error' => $e));
            throw new Exception($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function errorInfo()
    {
        try {
            return $this->getDriver()->errorInfo();
        } catch (\PDOException $e) {
            $this->log(Logger::ERROR, $e->getMessage(), array('error' => $e));
            throw new Exception($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function commit()
    {
        try {
            $this->log(Logger::DEBUG, 'Transaction commit');
            return $this->getDriver()->commit();
        } catch (\PDOException $e) {
            $this->log(Logger::ERROR, $e->getMessage(), array('error' => $e));
            throw new Exception($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Execute a statement for given values
     * @param object $statement
     * @param array $values
     * @return bool
     * @throws Exception
     */
    public function execute($statement, $values = array())
    {
        if (!$statement instanceof \PDOStatement) {
            $this->log(Logger::ERROR, 'Statement not in right format', array('statement' => $statement));
            throw new Exception('Statement not in right format');
        }

        try {
            $this->log(Logger::DEBUG, 'Executing query', array('values' => $values));
            return $statement->execute($values);
        } catch (\PDOException $e) {
            $this->log(Logger::ERROR, $e->getMessage(), array('exception' => $e, 'values' => $values));
            throw new Exception($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Prepare a SQL string to a statement
     * @param string $sql
     * @param array $options
     * @return \PDOStatement
     * @throws Exception
     */
    public function prepare($sql, array $options = array())
    {
        try {
            $this->log(Logger::DEBUG, 'Preparing query', array('sql' => $sql, 'options' => $options));
            return $this->getDriver()->prepare($sql, $options);
        } catch (\PDOException $e) {
            $this->log(Logger::ERROR, $e->getMessage(), array('exception' => $e, 'sql' => $sql));
            throw new Exception($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Retrieve id inserted
     * @param string $name optional
     * @return string
     * @throws Exception
     */
    public function lastInsertId($name = null)
    {
        try {
            return $this->getDriver()->lastInsertId($name);
        } catch (\PDOException $e) {
            $this->log(Logger::ERROR, $e->getMessage(), array('error' => $e));
            throw new Exception($e->getMessage(), (int)$e->getCode(), $e);
        }
    }
}
