<?php
namespace FMUP\Db\Driver\Pdo;

use FMUP\Db\Driver\Pdo;
use FMUP\Db\Exception;
use FMUP\Logger;

class Sqlite extends Pdo
{
    protected $instance = null;

    /**
     * @return null|\PDO
     * @throws Exception
     */
    public function getDriver()
    {
        if (is_null($this->instance)) {
            try {
                $this->instance = $this->getPdo($this->getDsn());
            } catch (\Exception $e) {
                $this->log(Logger::CRITICAL, 'Unable to connect database', (array)$this->getSettings());
                throw new Exception('Unable to connect database', $e->getCode(), $e);
            }
            $this->defaultConfiguration($this->instance);
        }
        return $this->instance;
    }

    /**
     * @param $dsn
     * @return \PDO
     * @codeCoverageIgnore
     */
    protected function getPdo($dsn)
    {
        return new \PDO($dsn);
    }

    protected function defaultConfiguration(\Pdo $instance)
    {
        $instance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $instance->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        return $this;
    }

    protected function getDsn()
    {
        return $this->getDsnDriver() . ':' . $this->getHost() . DIRECTORY_SEPARATOR . $this->getDatabase() . '.sqlite';
    }

    protected function getDsnDriver()
    {
        return 'sqlite';
    }

    protected function getHost()
    {
        return !is_null($this->getSettings('host'))
            ? $this->getSettings('host')
            : __DIR__ . '/../../../../../../../logs';
    }

    protected function getDatabase()
    {
        return !is_null($this->getSettings('database')) ? $this->getSettings('database') : 'pdo_sqlite';
    }
}
