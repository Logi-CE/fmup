<?php
namespace FMUP\Db\Driver;

use FMUP\Db\DbInterface;
use FMUP\Db\Exception;

class Pdo implements DbInterface
{
    private $instance = null;
    private $params = array();
    private $fetchMode = \PDO::FETCH_ASSOC;

    const CHARSET_UTF8 = 'utf8';

    /**
     * @param array $params
     */
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
        if (is_null($this->instance)) {
            $this->instance = new \PDO($this->getDsn(), $this->getLogin(), $this->getPassword(), $this->getOptions());
            if (!$this->instance) {
                throw new Exception('Unable to connect database');
            }
            $this->defaultConfiguration($this->instance);
        }
        return $this->instance;
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
     * Get string for dsn construction
     * @return string
     */
    protected function getDsn()
    {
        $driver = $this->getDsnDriver();
        $host = $this->getHost();
        $database = $this->getDatabase();
        $dsn = $driver . ":host=" . $host;
        if (!is_null($database)) {
            $dsn .= ";dbname=" . $database;
        }
        return $dsn;
    }

    /**
     * Database to connect to
     * @return string|null
     */
    protected function getDatabase()
    {
        return isset($this->params['database']) ? $this->params['database'] : null;
    }

    /**
     * Host to connect to
     * @return string
     */
    protected function getHost()
    {
        return isset($this->params['host']) ? $this->params['host'] : 'localhost';
    }

    /**
     * Dsn Driver to use
     * @return string
     */
    protected function getDsnDriver()
    {
        return isset($this->params['driver']) ? $this->params['driver'] : 'mysql';
    }

    /**
     * Options for PDO Settings
     * @return array
     */
    protected function getOptions()
    {
        return array(
            \PDO::ATTR_PERSISTENT => (bool)(
            isset($this->params['PDOBddPersistant']) ? $this->params['PDOBddPersistant'] : false
            ),
            \PDO::ATTR_EMULATE_PREPARES => true
        );
    }

    /**
     * Charset to use
     * @return string
     */
    protected function getCharset()
    {
        return isset($params['charset']) ? $params['charset'] : self::CHARSET_UTF8;
    }

    /**
     * Login to use
     * @return string
     */
    protected function getLogin()
    {
        return isset($this->params['login']) ? $this->params['login'] : '';
    }

    /**
     * Password to use
     * @return string
     */
    protected function getPassword()
    {
        return isset($this->params['password']) ? $this->params['password'] : '';
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
                throw new Exception('Transaction already opened');
            }
            return $this->getDriver()->beginTransaction();
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function rollback()
    {
        try {
            return $this->getDriver()->rollBack();
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
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
            throw new Exception($e->getMessage(), $e->getCode(), $e);
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
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function commit()
    {
        try {
            return $this->getDriver()->commit();
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param $sql
     * @return bool
     * @throws Exception
     */
    public function rawExecute($sql)
    {
        try {
            return $this->getDriver()->prepare($sql)->execute();
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
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
            throw new Exception('Statement not in right format');
        }

        try {
            return $statement->execute($values);
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
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
            return $this->getDriver()->prepare($sql, $options);
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
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
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Fetch a row for a given statement
     * @param object $statement
     * @return array
     * @throws Exception
     */
    public function fetchRow($statement)
    {
        if (!$statement instanceof \PDOStatement) {
            throw new Exception('Statement not in right format');
        }

        try {
            return $statement->fetch($this->getFetchMode());
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
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
            throw new Exception('Statement not in right format');
        }

        try {
            return $statement->fetchAll($this->getFetchMode());
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return int
     */
    public function getFetchMode()
    {
        return $this->fetchMode;
    }

    /**
     * @param int $fetchMode
     * @return $this
     */
    public function setFetchMode($fetchMode = \PDO::FETCH_ASSOC)
    {
        if ($fetchMode) {
            $this->fetchMode = (int)$fetchMode;
        }
        return $this;
    }
}
