<?php
namespace FMUP;

use FMUP\Db\Factory;

/**
 * Class Db
 * @package FMUP
 */
class Db
{
    protected $driver = Factory::DRIVER_PDO;
    protected $params = array();
    private $driverInstance = null;

    public function __construct($params = array())
    {
        $this->driver = isset($params['db_driver']) ? $params['db_driver'] : Factory::DRIVER_PDO;
        $this->params = $params;
    }

    /**
     * @return Db\DbInterface|null
     * @throws Db\Exception
     */
    public function getDriver()
    {
        if (!is_null($this->driverInstance)) {
            return $this->driverInstance;
        }

        $this->driverInstance = Factory::create($this->driver, $this->params);

        return $this->driverInstance;
    }

    public function query($sql, $params = array())
    {
        $statement = $this->getDriver()->prepare($sql);

        return $this->getDriver()->execute($statement, $params);
    }

    /**
     * @param $sql
     * @param array $params
     * @return mixed
     * @throws Db\Exception
     */
    public function fetchAll($sql, $params = array())
    {
        $statement = $this->getDriver()->prepare($sql);
        $this->getDriver()->execute($statement, $params);
        /**
         * @todo tune with DB cursor if available // move this to driver
         */
        $arrayResult = $this->getDriver()->fetchAll($statement);
        return empty($arrayResult) ? array() : new \ArrayIterator($arrayResult);
    }

    public function fetchRow($sql, $params)
    {
        $statement = $this->getDriver()->prepare($sql);
        $this->getDriver()->execute($statement, $params);

        return $this->getDriver()->fetchRow($statement);
    }

    public function beginTransaction()
    {
        return $this->getDriver()->beginTransaction();
    }

    public function commit()
    {
        return $this->getDriver()->commit();
    }

    public function rollback()
    {
        return $this->getDriver()->rollback();
    }

    public function lastInsertId()
    {
        return $this->getDriver()->lastInsertId();
    }

    /*
     * Everything beneath this line is here for backwards compatibility purpose and is deprecated
     */

    /**
     * @deprecated use fetchAll() instead
     * @param $sql
     * @return mixed
     */
    public function requete($sql)
    {
        try {
            return $this->fetchAll($sql);
        } catch (\Exception $e) {
            new \Error($e->getMessage().'<br/>'.$sql, 99, $e->getFile(), $e->getLine());
        }
    }

    /**
     * @deprecated use query() instead
     * @param $sql
     * @return mixed
     */
    public function requeteUtf8($sql)
    {
        $resultat = self::requete($sql);
        array_walk_recursive($resultat, create_function('&$item, $index', '$item = utf8_encode($item);'));

        return $resultat;
    }

    /**
     * @deprecated use fetchRow() instead
     * @param $sql
     * @return array
     * @throws Db\Exception
     */
    public function requeteUneLigne($sql)
    {
        $statement = $this->getDriver()->prepare($sql);
        $this->getDriver()->execute($statement, NULL);

        return $this->getDriver()->fetchRow($statement);
    }

    /**
     * @deprecated use fetchRow() instead
     * @param $sql
     * @return array
     */
    public function requete_une_ligne($sql)
    {
        return $this->requeteUneLigne($sql);
    }

    /**
     * @deprecated use fetchAll() instead
     * @param $sql
     * @return array
     */
    public function exportQuery($sql)
    {
        return $this->requeteUneLigne($sql);
    }

    /**
     * @deprecated use fetchAll() instead
     * @param $sql
     * @return array
     */
    public function export_query($sql)
    {
        return $this->exportQuery($sql);
    }

    /**
     * @deprecated use fetchAll() instead
     * @param $stmt
     * @return mixed
     * @throws Db\Exception
     */
    public function exportFetchArray($stmt)
    {
        return $this->getDriver()->fetchAll($stmt);
    }

    /**
     * @deprecated use fetchAll() instead
     * @param $stmt
     * @return mixed
     */
    public function export_fetch_array($stmt)
    {
        return $this->exportFetchArray($stmt);
    }

    /**
     * @deprecated use query() instead
     * @param $sql
     * @param string $commentaire
     * @param bool $logguer_requete
     * @param array $params
     * @return array|bool|int
     */
    public function execute($sql, $commentaire = '', $logguer_requete = true, $params = array(array()))
    {
        try {
            if (strtoupper(substr($sql, 0, 7)) == "UPDATE ") {
                $type_execute = "UPDATE";
            } elseif (strtoupper(substr($sql, 0, 7)) == "INSERT ") {
                $type_execute = "INSERT";
            } elseif (strtoupper(substr($sql, 0, 7)) == "DELETE ") {
                $type_execute = "DELETE";
            } elseif (strtoupper(substr($sql, 0, 12)) == "BULK INSERT ") {
                $type_execute = "BULK INSERT";
            } else {
                $type_execute = "?";
            }

            $new_records = array();
            $nb_rows = 0;

            if (((strpos($sql, "?") !== false) || (strpos($sql, ":") !== false)) && count($params[0]) == 0) {
                $duree = microtime(1);
                $memoire = memory_get_usage();

                $stmt = $this->getDriver()->rawExecute($sql);

                $duree -= microtime(1);
                $memoire -= memory_get_usage();
                \Console::enregistrer(array('requete' => $sql, 'duree' => round(abs($duree), 4), 'memoire' => round(abs($memoire) / 1000000, 3), 'resultat' => $stmt), LOG_SQL);

                if ($type_execute=="INSERT") {
                    $id_new_record = $this->getDriver()->lastInsertId();
                    $new_records = array_merge($new_records, array($id_new_record));
                }
            } else {
                $stmt = $this->getDriver()->prepare($sql);

                foreach ($params as $param) {
                    foreach ($param as $cle => $valeur) {
                        $param[$cle] = str_replace($tab_script, $tab_script_replace, $valeur);
                    }
                    $duree = microtime(1);
                    $memoire = memory_get_usage();
                    $nb_rows = $nb_rows + $this->getDriver()->execute($stmt, $param);

                    $duree -= microtime(1);
                    $memoire -= memory_get_usage();
                    \Console::enregistrer(array('requete' => $sql, 'duree' => round(abs($duree), 4), 'memoire' => round(abs($memoire) / 1000000, 3), 'resultat' => $nb_rows), LOG_SQL);

                    if ($type_execute=="INSERT") {
                        $id_new_record = $this->getDriver()->lastInsertId();
                        $new_records = array_merge($new_records, array($id_new_record));
                    }
                }
            }

        } catch (\Exception $e) {
            new \Error($e->getMessage().'<br/>'.$sql, 99, $e->getFile(), $e->getLine());
        }

        if ($type_execute=="INSERT") {
            if (count($new_records) == 1) {
                $new_records = $new_records[0];
            } else {
                $new_records = false;
            }
            return $new_records;
        } else {
            return $nb_rows;
        }
    }

    /**
     * @deprecated use beginTransaction() instead
     * @return bool
     * @throws Db\Exception
     */
    public function beginTrans()
    {
        return $this->beginTransaction();
    }

    /**
     * @deprecated use commit() instead
     * @return bool
     */
    public function commitTrans()
    {
        return $this->commit();
    }

    /**
     * @deprecated use rollback() instead
     * @return mixed
     */
    public function rollbackTrans()
    {
        return $this->rollback();
    }
}
