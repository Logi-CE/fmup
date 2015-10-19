<?php
namespace FMUP\Db\Driver;

use FMUP\Db\DbInterface;
use FMUP\Db\Exception;

class Pdo implements DbInterface
{
    protected $instance = null;
    protected $params = array();
    const CHARSET_UTF8 = 'utf8';

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
            \PDO::ATTR_PERSISTENT => (bool)(isset($this->params['PDOBddPersistant']) ? $this->params['PDOBddPersistant'] : false),
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
