<?php
namespace FMUP\Db\Driver\Pdo;

use FMUP\Db\Driver\Pdo;

class Odbc extends Pdo
{
    protected $instance = null;

    protected function defaultConfiguration(\Pdo $instance)
    {
        $instance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $instance->setAttribute(\PDO::ATTR_TIMEOUT, 10.0);
        return $this;
    }

    protected function getDsn()
    {
        $host = $this->getHost();
        $database = $this->getDatabase();
        $dsn = 'odbc:Driver={' . $this->getDsnDriver() . '}';
        if ($host) {
            $dsn .= ';Server={' . $host . '}';
        }
        if ($database) {
            $dsn .= ';Database={' . $database . '}';
        }
        return $dsn;
    }
}
