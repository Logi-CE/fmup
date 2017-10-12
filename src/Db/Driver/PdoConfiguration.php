<?php
/**
 * PdoConfigurationConfiguration.php
 * @author: jmoulin@castelis.com
 */

namespace FMUP\Db\Driver;

use FMUP\Db\DbInterface;
use FMUP\Db\Exception;
use FMUP\Logger;

abstract class PdoConfiguration implements DbInterface, Logger\LoggerInterface
{
    use Logger\LoggerTrait;

    private $settings = array();
    private $fetchMode = \PDO::FETCH_ASSOC;
    private $instance = null;

    /**
     * @return string
     */
    protected function getLoggerName()
    {
        return Logger\Channel\System::NAME;
    }

    /**
     * @param array $params
     */
    public function __construct($params = array())
    {
        $this->settings = $params;
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
            \PDO::ATTR_EMULATE_PREPARES => true,
            \PDO::MYSQL_ATTR_LOCAL_INFILE => true,
        );
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

    /**
     * @param string $sql
     * @return bool
     * @throws Exception
     */
    public function rawExecute($sql)
    {
        try {
            $this->log(Logger::DEBUG, 'Raw execute query', array('sql' => $sql));
            return $this->getDriver()->prepare($sql)->execute();
        } catch (\PDOException $e) {
            $this->log(Logger::ERROR, $e->getMessage(), array('error' => $e));
            throw new Exception($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Fetch a row for a given statement
     * @param object $statement
     * @param int $cursorOrientation Cursor orientation (next by default)
     * @param int $cursorOffset Cursor offset (0 by default)
     * @return array
     * @throws Exception
     */
    public function fetchRow($statement, $cursorOrientation = self::CURSOR_NEXT, $cursorOffset = 0)
    {
        if (!$statement instanceof \PDOStatement) {
            $this->log(Logger::ERROR, 'Statement not in right format', array('statement' => $statement));
            throw new Exception('Statement not in right format');
        }

        try {
            return $statement->fetch($this->getFetchMode(), (int)$cursorOrientation, (int)$cursorOffset);
        } catch (\PDOException $e) {
            $this->log(Logger::ERROR, $e->getMessage(), array('error' => $e));
            throw new Exception($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * @return \PDO
     * @throws Exception
     */
    public function getDriver()
    {
        if (is_null($this->instance)) {
            try {
                $this->instance = $this->getPdo(
                    $this->getDsn(),
                    $this->getLogin(),
                    $this->getPassword(),
                    $this->getOptions()
                );
            } catch (\Exception $e) {
                $this->log(Logger::CRITICAL, 'Unable to connect database', (array)$this->getSettings());
                throw new Exception('Unable to connect database', $e->getCode(), $e);
            }
            $this->defaultConfiguration($this->instance);
        }
        return $this->instance;
    }

    /**
     * @param \Pdo $instance
     * @return $this
     */
    abstract protected function defaultConfiguration(\Pdo $instance);

    /**
     * @param string $dsn
     * @param string $login
     * @param string $password
     * @param array $options
     * @return \PDO
     */
    protected function getPdo($dsn, $login = null, $password = null, array $options = null)
    {
        return new \PDO($dsn, $login, $password, $options);
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
}
