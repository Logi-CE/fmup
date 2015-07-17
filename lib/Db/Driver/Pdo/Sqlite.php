<?php
namespace FMUP\Db\Driver\Pdo;

use FMUP\Db\Exception;
use FMUP\Db\Driver\Pdo;

class Sqlite extends Pdo
{
    protected $instance = NULL;

    public function getDriver()
    {
        if (!is_null($this->instance)) {
            return $this->instance;
        }

        //$driver = isset($this->params['driver']) ? $this->params['driver'] : 'mysql';
        $host = isset($this->params['host']) ? $this->params['host'] : BASE_PATH . implode(DIRECTORY_SEPARATOR, array('logs'));
        $database = isset($this->params['database']) ? $this->params['database'] : 'pdo_sqlite';
        $dsn = 'sqlite:' . $host . DIRECTORY_SEPARATOR . $database . '.sqlite';
        $this->instance = new \PDO($dsn);
        if (!$this->instance) {
            throw new Exception('Unable to connect database');
        }
        $this->instance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->instance->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        return $this->instance;
    }
}
