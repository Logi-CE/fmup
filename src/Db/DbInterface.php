<?php
namespace FMUP\Db;

interface DbInterface
{
    const CURSOR_FIRST = \Pdo::FETCH_ORI_FIRST;
    const CURSOR_NEXT = \Pdo::FETCH_ORI_NEXT;
    const CURSOR_ABS = \Pdo::FETCH_ORI_ABS;

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
     * @param object|\PDOStatement $statement
     * @param array $values
     * @return bool
     * @throws Exception
     * @throws \Exception
     */
    public function execute($statement, $values = array());

    /**
     * Prepare a SQL string to a statement
     * @param string $sql
     * @return object|\PDOStatement
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
     * @param object|\PDOStatement $statement
     * @param int $cursorOrientation Cursor orientation (next by default)
     * @param int $cursorOffset Cursor offset (0 by default)
     * @return array
     * @throws Exception
     * @throws \Exception
     */
    public function fetchRow($statement, $cursorOrientation = self::CURSOR_NEXT, $cursorOffset = 0);

    /**
     * Fetch all rows for a given statement
     * @param object|\PDOStatement $statement
     * @return array
     * @throws Exception
     * @throws \Exception
     */
    public function fetchAll($statement);

    /**
     * Force reconnection
     * @return $this
     */
    public function forceReconnect();

    /**
     * Retrieve the internal driver
     * @return mixed
     */
    public function getDriver();

    /**
     * Returns number of affected rows of a statement
     * @param mixed $statement
     * @return int
     */
    public function count($statement);
}
