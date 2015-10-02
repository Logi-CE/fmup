<?php
namespace FMUP\Db\Driver\Pdo;

use FMUP\Db\Driver\Pdo;
use FMUP\Db\Exception;

class Sqlite extends Pdo
{
    protected $instance = null;

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
        return 'sqlite:' . $this->getHost() . DIRECTORY_SEPARATOR . $this->getDatabase() . '.sqlite';
    }


    protected function getHost()
    {
        return isset($this->params['host']) ? $this->params['host'] : BASE_PATH . implode(DIRECTORY_SEPARATOR, array('logs'));
    }

    protected function getDatabase()
    {
        return isset($this->params['database']) ? $this->params['database'] : 'pdo_sqlite';
    }
}
