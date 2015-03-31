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


    public function beginTransaction()
    {
        if ($this->getDriver()->inTransaction()) {
            throw new Exception('Transaction already opened');
        }
        return $this->getDriver()->beginTransaction();
    }

    public function rollback()
    {
        return $this->getDriver()->rollBack();
    }

    public function errorCode()
    {
        return $this->getDriver()->errorCode();
    }

    public function errorInfo()
    {
        return $this->getDriver()->errorInfo();
    }

    public function commit()
    {
        return $this->getDriver()->commit();
    }

    public function rawExecute($sql)
    {
        return $this->getDriver()->prepare($sql)->execute();
    }

    public function execute($statement, $values = array())
    {
        if (!$statement instanceof \PDOStatement) {
            throw new Exception('Statement not in right format');
        }

        return $statement->execute($values);
    }

    public function prepare($sql)
    {
        return $this->getDriver()->prepare($sql);
    }

    public function lastInsertId()
    {
        return $this->getDriver()->lastInsertId();
    }

    public function fetchRow($statement)
    {
        if (!$statement instanceof \PDOStatement) {
            throw new Exception('Statement not in right format');
        }

        return $statement->fetch(\PDO::FETCH_ASSOC);
    }

    public function fetchAll($statement)
    {
        if (!$statement instanceof \PDOStatement) {
            throw new Exception('Statement not in right format');
        }

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }
}
