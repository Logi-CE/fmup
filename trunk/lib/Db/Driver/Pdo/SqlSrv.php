<?php
namespace FMUP\Db\Driver\Pdo;

use FMUP\Db\Exception;

class SqlSrv extends \FMUP\Db\Driver\Pdo
{
    protected $instance = NULL;

    public function getDriver()
    {
        if (!is_null($this->instance)) {
            return $this->instance;
        }

        //$driver = isset($this->params['driver']) ? $this->params['driver'] : 'mysql';
        $host = isset($this->params['host']) ? $this->params['host'] : 'localhost';
        $database = isset($this->params['database']) ? $this->params['database'] : null;
        $dsn = 'sqlsrv:Server={'.$host.'}';
        $dsn .= ';Database={'.$database.'};';

        $charset = isset($params['charset']) ? $params['charset'] : 'utf8';
        $login = isset($this->params['login']) ? $this->params['login'] : '';
        $password = isset($this->params['password']) ? $this->params['password'] : '';
        $options = array(
            \PDO::ATTR_PERSISTENT => (bool) (isset($this->params['PDOBddPersistant']) ? $this->params['PDOBddPersistant'] : false),
            \PDO::ATTR_EMULATE_PREPARES => true
        );
        if ($charset == 'utf8') {
            $options[\PDO::SQLSRV_ENCODING_UTF8] = true;
        }
        $this->instance = new \PDO($dsn, $login, $password, $options);
        if (!$this->instance) {
            throw new Exception('Unable to connect database');
        }
        $this->instance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        //$this->instance->setAttribute(\PDO::ATTR_TIMEOUT, 10.0);

        return $this->instance;
    }
}
