<?php
if (!defined('MSSQL')) define('MSSQL', 'mssql');
if (!defined('MYSQL')) define('MYSQL', 'mysql');

/**
 * Classe de connexion à une base de données
 * @version 1.0
 * @deprecated use \FMUP\Db instead
 * @see \FMUP\Db
 */
class DbConnectionMysql
{
    protected $conn; // la connexion
    protected $driver;
    protected $charset;

    static $instance = null;

    public static function getInstance ($params)
    {
        if (self::$instance == null) {
            self::$instance = new DbConnectionMysql($params);
        }
        return self::$instance;
    }

    /**
     * Constructeur
     **/
    public function __construct($params)
    {
        if (isset($params['host'], $params['login'], $params['password'], $params['database'], $params['driver'], $params['PDOBddPersistant'])) {
            try {
                if (isset($params['charset'])) {
                    $this->charset = $params['charset'];
                } else {
                    $this->charset = "utf8";
                }
                $this->driver = $params['driver'];
                $dsn          = $this->driver.":host=".$params['host'].";dbname=".$params['database'];
                $this->conn   = new PDO(
                    $dsn,
                    $params['login'],
                    $params['password'],
                    array(
                        PDO::ATTR_PERSISTENT => $params['PDOBddPersistant'],
                        PDO::ATTR_EMULATE_PREPARES => true
                    )
                );
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // les erreurs lanceront des exceptions
                $this->conn->setAttribute(PDO::ATTR_TIMEOUT, 10.0);
                $this->conn->exec('SET NAMES '.$this->charset);
                $this->conn->exec('SET CHARACTER SET '.$this->charset);
                if (!$this->conn) {
                    throw new Error(Error::connexionBDD());
                } else {
                    Console::enregistrer($params['host'].'/'.$params['database'].' ('.$params['driver'].')', LOG_CONNEXION);
                }
            } catch (Exception $e) {
                throw new Error(Error::connexionBDD());
            }
        } else {
            throw new Error(Error::connexionBDD());
        }
    }

    /**
     * Requete a la base de donnees
     * @return Tableau à 2 dimensions (enregistrements / champs)
     */
    public function requete($sql, $params = array(array()))
    {
        try {
            $rows = array();
            $stmt = $this->conn->prepare($sql);


            foreach ($params as $param) {
                $duree = microtime(1);
                $memoire = memory_get_usage();
                
                $stmt->execute($param);
                $rows = array_merge($rows, $stmt->fetchAll(PDO::FETCH_ASSOC));
                
                $duree -= microtime(1);
                $memoire -= memory_get_usage();
                Console::enregistrer(array('requete' => $sql, 'duree' => round(abs($duree), 4), 'memoire' => round(abs($memoire) / 1000000, 3), 'resultat' => $stmt->rowCount()), LOG_SQL);

            }
            $stmt->closeCursor();

        } catch (Exception $e) {
            new Error($e->getMessage().'<br/>'.$sql, 99, $e->getFile(), $e->getLine());
        }

        return $rows;
    }

    public function requeteUtf8($sql)
    {
        $resultat = self::requete($sql);
        array_walk_recursive($resultat, create_function('&$item, $index', '$item = utf8_encode($item);'));
        return $resultat;
    }
    /**
     * Requete a la base de donnees
     * @return Une seule ligne
     */
    public function requeteUneLigne($sql, $params = array(array()))
    {
        $rows = $this->requete($sql, $params);
        if ($rows) {
            return $rows[0];
        } else {
            return array();
        }
    }

    public function exportQuery($sql)
    {
        try {
            $rows = array();
            $stmt = $this->conn->prepare($sql);


            $duree = microtime(1);
            $memoire = memory_get_usage();
            
            $stmt->execute();
            
            $duree -= microtime(1);
            $memoire -= memory_get_usage();
            Console::enregistrer(array('requete' => $sql, 'duree' => round(abs($duree), 4), 'memoire' => round(abs($memoire) / 1000000, 3), 'resultat' => $stmt->rowCount()), LOG_SQL);

        } catch (Exception $e) {
            new Error($e->getMessage().'<br/>'.$sql, 99, $e->getFile(), $e->getLine());
        }

        return $stmt;
    }

    public function exportFetchArray($stmt)
    {
        $resultat = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$resultat) {
            $stmt->closeCursor();
        }
        return $resultat;
    }

    /**
     * Requete de mise à jour (update, insert, commit)
     * @return Le nombre de lignes affectées (update, delete) ou bien l'id inséré (insert)
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

            $id_new_record = -1;
            $new_records = array();
            $nb_rows = 0;

            if (((strpos($sql, "?") !== false) || (strpos($sql, ":") !== false)) && count($params[0]) == 0) {
                $duree = microtime(1);
                $memoire = memory_get_usage();

                $stmt = $this->conn->exec($sql);

                $duree -= microtime(1);
                $memoire -= memory_get_usage();
                Console::enregistrer(array('requete' => $sql, 'duree' => round(abs($duree), 4), 'memoire' => round(abs($memoire) / 1000000, 3), 'resultat' => $stmt), LOG_SQL);

                if ($type_execute=="INSERT") {
                    $id_new_record = $this->conn->lastInsertId();
                    $new_records = array_merge($new_records, array($id_new_record));
                }
            } else {
                $stmt = $this->conn->prepare($sql);

                foreach ($params as $param) {
                    foreach ($param as $cle => $valeur) {
                        $param[$cle] = str_replace($tab_script, $tab_script_replace, $valeur);
                    }
                    $duree = microtime(1);
                    $memoire = memory_get_usage();
                    $nb_rows = $nb_rows + $stmt->execute($param);

                    $duree -= microtime(1);
                    $memoire -= memory_get_usage();
                    Console::enregistrer(array('requete' => $sql, 'duree' => round(abs($duree), 4), 'memoire' => round(abs($memoire) / 1000000, 3), 'resultat' => $nb_rows), LOG_SQL);

                    if ($type_execute=="INSERT") {
                        $id_new_record = $this->conn->lastInsertId();
                        $new_records = array_merge($new_records, array($id_new_record));
                    }
                    $stmt->closeCursor();
                }
            }

        } catch (Exception $e) {
            new Error($e->getMessage().'<br/>'.$sql, 99, $e->getFile(), $e->getLine());
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

    public function beginTransaction()
    {
        $this->conn->beginTransaction();
    }

    public function commit()
    {
        $this->conn->commit();
    }

    public function rollBack()
    {
        $this->conn->rollBack();
    }

    public function lastInsertId()
    {
        if ($this->conn instanceof PDO) {
            return $this->conn->lastInsertId();
        }
    }
}
