<?php
namespace FMUP\Db\Driver;

use FMUP\Db\DbInterface;
use FMUP\Db\Exception;

class Pdo implements DbInterface
{
    protected $instance = NULL;
    protected $params = array();

    public function __construct($params = array())
    {
        $this->params = $params;
    }

    /**
     * @return null|\PDO
     * @throws Exception
     */
    public function getDriver()
    {
        if (!is_null($this->instance)) {
            return $this->instance;
        }

        $driver = isset($this->params['driver']) ? $this->params['driver'] : 'mysql';
        $host = isset($this->params['host']) ? $this->params['host'] : 'localhost';
        $database = isset($this->params['database']) ? $this->params['database'] : null;
        $dsn = $driver . ":host=" . $host;
        if (!is_null($database)) {
            $dsn .= ";dbname=" . $database;
        }
        $charset = isset($params['charset']) ? $params['charset'] : 'utf8';
        $login = isset($this->params['login']) ? $this->params['login'] : '';
        $password = isset($this->params['password']) ? $this->params['password'] : '';
        $options = array(
            \PDO::ATTR_PERSISTENT => (bool) (isset($this->params['PDOBddPersistant']) ? $this->params['PDOBddPersistant'] : false),
            \PDO::ATTR_EMULATE_PREPARES => true
        );
        $this->instance = new \PDO($dsn, $login, $password, $options);
        if (!$this->instance) {
            throw new Exception('Unable to connect database');
        }
        $this->instance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->instance->setAttribute(\PDO::ATTR_TIMEOUT, 10.0);
        $this->instance->exec('SET NAMES ' . $charset);
        $this->instance->exec('SET CHARACTER SET '. $charset);

        return $this->instance;
    }


    /**
     * Begin a transaction
     * @return bool
     * @throws Exception
     */
    public function beginTransaction()
    {
        if ($this->getDriver()->inTransaction()) {
            throw new Exception('Transaction already opened');
        }
        return $this->getDriver()->beginTransaction();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function rollback()
    {
        return $this->getDriver()->rollBack();
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function errorCode()
    {
        return $this->getDriver()->errorCode();
    }

    /**
     * @return array
     * @throws Exception
     */
    public function errorInfo()
    {
        return $this->getDriver()->errorInfo();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function commit()
    {
        return $this->getDriver()->commit();
    }

    /**
     * @param $sql
     * @return bool
     * @throws Exception
     */
    public function rawExecute($sql)
    {
        return $this->getDriver()->prepare($sql)->execute();
    }

    /**
     * Execute a statement for given values
     * @param object $statement
     * @param array $values
     * @return bool
     * @throws Exception
     * @throws \Exception
     */
    public function execute($statement, $values = array())
    {
        if (!$statement instanceof \PDOStatement) {
            throw new Exception('Statement not in right format');
        }

        return $statement->execute($values);
    }

    /**
     * Prepare a SQL string to a statement
     * @param string $sql
     * @return \PDOStatement
     * @throws Exception
     * @throws \Exception
     */
    public function prepare($sql)
    {
        return $this->getDriver()->prepare($sql);
    }

    /**
     * Retrieve id inserted
     * @param string $name optional
     * @return string
     * @throws Exception
     * @throws \Exception
     */
    public function lastInsertId($name = null)
    {
        return $this->getDriver()->lastInsertId($name);
    }

    /**
     * Fetch a row for a given statement
     * @param object $statement
     * @return array
     * @throws Exception
     * @throws \Exception
     */
    public function fetchRow($statement)
    {
        if (!$statement instanceof \PDOStatement) {
            throw new Exception('Statement not in right format');
        }

        return $statement->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Fetch all rows for a given statement
     * @param object $statement
     * @return array
     * @throws Exception
     * @throws \Exception
     */
    public function fetchAll($statement)
    {
        if (!$statement instanceof \PDOStatement) {
            throw new Exception('Statement not in right format');
        }

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }
}
