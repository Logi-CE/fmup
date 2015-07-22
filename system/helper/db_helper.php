<?php

/**
 * Retrieve current Database driver
 */
class DbHelper
{
    protected static $instance;

    /**
     * Returns current database driver
     * @param array $params
     * @return DbConnectionMssql|DbConnectionMysql
     */
    public static function get($params = array())
    {
        if (is_null(self::$instance)) {
            if (empty($params)) {
                $params = Config::parametresConnexionDb();
            }
            switch($params['driver']) {
                case 'mssql':
                    self::$instance = new DbConnectionMssql($params);
                    break;
                case 'mysql' :
                default:
                    self::$instance = new DbConnectionMysql($params);
                    break;
            }
        }

        return self::$instance;
    }
}
