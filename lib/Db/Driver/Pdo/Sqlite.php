<?php
namespace FMUP\Db\Driver\Pdo;

use FMUP\Db\Driver\Pdo;
use FMUP\Db\Exception;

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
            $this->instance = new \PDO($this->getDsn());
            if (!$this->instance) {
                throw new Exception('Unable to connect database');
            }
            $this->defaultConfiguration($this->instance);
        }
        return $this->instance;
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
            : BASE_PATH . implode(DIRECTORY_SEPARATOR, array('logs'));
    }

    protected function getDatabase()
    {
        return !is_null($this->getSettings('database')) ? $this->getSettings('database') : 'pdo_sqlite';
    }
}
