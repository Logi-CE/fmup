<?php
namespace FMUP\Db;

interface DbInterface
{
    /**
     * Construct instance must allow array of parameters
     * @param array $params
     */
    public function __construct($params);

    /**
     * Begin a transaction
     * @return bool
     * @throws Exception
     * @throws \Exception
     */
    public function beginTransaction();

    /**
     * Cancel everything that happened during transaction
     * @return bool
     * @throws Exception
     * @throws \Exception
     */
    public function rollback();

    /**
     * Get last error code
     * @return mixed
     * @throws Exception
     * @throws \Exception
     */
    public function errorCode();

    /**
     * Return list of all errors
     * @return array
     * @throws Exception
     * @throws \Exception
     */
    public function errorInfo();

    /**
     * Saves everything that happened during transaction
     * @return bool
     * @throws Exception
     * @throws \Exception
     */
    public function commit();

    /**
     * Execute an sql string directly
     * @param string $sql
     * @return bool
     * @throws Exception
     * @throws \Exception
     */
    public function rawExecute($sql);

    /**
     * Execute a statement for given values
     * @param object $statement
     * @param array $values
     * @return bool
     * @throws Exception
     * @throws \Exception
     */
    public function execute($statement, $values = array());

    /**
     * Prepare a SQL string to a statement
     * @param string $sql
     * @return object
     * @throws Exception
     * @throws \Exception
     */
    public function prepare($sql);

    /**
     * Retrieve id inserted
     * @param string $name optional
     * @return string
     * @throws Exception
     * @throws \Exception
     */
    public function lastInsertId($name = null);

    /**
     * Fetch a row for a given statement
     * @param object $statement
     * @return array
     * @throws Exception
     * @throws \Exception
     */
    public function fetchRow($statement);

    /**
     * Fetch all rows for a given statement
     * @param object $statement
     * @return array
     * @throws Exception
     * @throws \Exception
     */
    public function fetchAll($statement);

    /**
     * Retrieve the internal driver
     * @return mixed
     */
    public function getDriver();
}
