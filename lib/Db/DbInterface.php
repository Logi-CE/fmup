<?php
namespace FMUP\Db;

interface DbInterface
{
    /**
     * Construct instance must allow array of parameters
     * @param array $params
     */
    public function __construct($params);

    public function beginTransaction();
    public function rollback();
    public function errorCode();
    public function errorInfo();
    public function commit();
    public function rawExecute($sql);
    public function execute($statement, $values = array());
    public function prepare($sql);
    public function lastInsertId();
    public function fetchRow($statement);
    public function fetchAll($statement);
    public function getDriver();
}
