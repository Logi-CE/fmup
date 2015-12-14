<?php
namespace FMUP\Db\Driver;

use FMUP\Db\DbInterface;
use FMUP\Db\Exception;
use FMUP\Logger;

class Pdo implements DbInterface, Logger\LoggerInterface
{
    use Logger\LoggerTrait;

    /**
     * @return string
     */
    protected function getLoggerName()
    {
        return Logger\Channel\System::NAME;
    }

    private $instance = null;
    private $settings = array();
    private $fetchMode = \PDO::FETCH_ASSOC;

    const CHARSET_UTF8 = 'utf8';

    /**
     * @param array $params
     */
    public function __construct($params = array())
    {
        $this->settings = $params;
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
                $this->log(Logger::CRITICAL, 'Unable to connect database', $this->getSettings());
                throw new Exception('Unable to connect database');
            }
            $this->defaultConfiguration($this->instance);
        }
        return $this->instance;
    }

    /**
     * Force reconnection
     * @return $this
     */
    public function forceReconnect()
    {
        $this->instance = null;
        return $this;
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
        return isset($this->settings['database']) ? $this->settings['database'] : null;
    }

    /**
     * Host to connect to
     * @return string
     */
    protected function getHost()
    {
        return isset($this->settings['host']) ? $this->settings['host'] : 'localhost';
    }

    /**
     * Dsn Driver to use
     * @return string
     */
    protected function getDsnDriver()
    {
        return isset($this->settings['driver']) ? $this->settings['driver'] : 'mysql';
    }

    /**
     * Retrieve settings
     * @param null $param
     * @return array|null
     */
    protected function getSettings($param = null)
    {
        return is_null($param) ? $this->settings : (isset($this->settings[$param]) ? $this->settings[$param] : null);
    }

    /**
     * Options for PDO Settings
     * @return array
     */
    protected function getOptions()
    {
        return array(
            \PDO::ATTR_PERSISTENT => (bool)(
            isset($this->settings['PDOBddPersistant']) ? $this->settings['PDOBddPersistant'] : false
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
        return isset($this->settings['charset']) ? $this->settings['charset'] : self::CHARSET_UTF8;
    }

    /**
     * Login to use
     * @return string
     */
    protected function getLogin()
    {
        return isset($this->settings['login']) ? $this->settings['login'] : '';
    }

    /**
     * Password to use
     * @return string
     */
    protected function getPassword()
    {
        return isset($this->settings['password']) ? $this->settings['password'] : '';
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
            return $this->getDriver()->beginTransaction();
        } catch (\PDOException $e) {
            $this->log(Logger::ERROR, $e->getMessage(), $this->getSettings());
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
            return $this->getDriver()->rollBack();
        } catch (\PDOException $e) {
            $this->log(Logger::ERROR, $e->getMessage(), $e);
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
            $this->log(Logger::ERROR, $e->getMessage(), $e);
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
            $this->log(Logger::ERROR, $e->getMessage(), $e);
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
            return $this->getDriver()->commit();
        } catch (\PDOException $e) {
            $this->log(Logger::ERROR, $e->getMessage(), $e);
            throw new Exception($e->getMessage(), (int)$e->getCode(), $e);
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
            $this->log(Logger::ERROR, $e->getMessage(), $e);
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
            $this->log(Logger::ERROR, $e->getMessage(), $e);
            throw new Exception($e->getMessage(), (int)$e->getCode(), $e);
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
            $this->log(Logger::ERROR, 'Statement not in right format', array('statement' => $statement));
            throw new Exception('Statement not in right format');
        }

        try {
            return $statement->fetch($this->getFetchMode());
        } catch (\PDOException $e) {
            $this->log(Logger::ERROR, $e->getMessage(), $e);
            throw new Exception($e->getMessage(), (int)$e->getCode(), $e);
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
            $this->log(Logger::ERROR, 'Statement not in right format', array('statement' => $statement));
            throw new Exception('Statement not in right format');
        }

        try {
            return $statement->fetchAll($this->getFetchMode());
        } catch (\PDOException $e) {
            $this->log(Logger::ERROR, $e->getMessage(), $e);
            throw new Exception($e->getMessage(), (int)$e->getCode(), $e);
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
            $this->log(Logger::DEBUG, 'Fetch Mode changed', array('fetchMode' => $fetchMode));
        }
        return $this;
    }
}
