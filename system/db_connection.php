<?php
/**
 * Classe de connexion à une base de données
 **/

if (!defined('MSSQL')) define('MSSQL', 'mssql');
if (!defined('MYSQL')) define('MYSQL', 'mysql');

/**
 * Class DbConnection
 * @deprecated use \FMUP\Db instead
 */
class DbConnection
{
    protected $conn; // la connexion
    protected $driver; // le moteur (MYSQL, MSSQL, ...)

    /**
     * Constructeur
     **/
    public function __construct($params)
    {
        if ((isset($params['host']) && isset($params['login']) && isset($params['password']) && isset($params['database']) && isset($params['driver']))) {
            // Driver
            $this->driver = $params['driver'];
            if (MSSQL !== $this->driver && MYSQL !== $this->driver) {
                throw new Error(Error::driverBdInconnu($this->driver));
            }

            // Connexion à la base de données
            if (MSSQL === $this->driver) {
                try {

                    $this->conn = new PDO('odbc:Driver={SQL Server};Server={'.$params['host'].'};Database={'.$params['database'].'};charset=UTF-8;', $params['login'], $params['password']);
                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                } catch (Exception $e) {
                    new Error($e->getMessage().'<br/>'.$sql, 99, $e->getFile(), $e->getLine());
                }

            } elseif (MYSQL === $this->driver) {
                $this->conn = mysql_connect($params['host'], $params['login'], $params['password'], true);
                mysql_query('SET CHARACTER SET utf8');
                mysql_query('SET NAMES utf8');
            }
            if (!$this->conn) {
                throw new Error(Error::connexionBDD());
            } else {
                Console::enregistrer($params['host'].'/'.$params['database'].' ('.$params['driver'].')', LOG_CONNEXION);
            }
            // Sélection de la base de données
            if (MSSQL === $this->driver) {
                /*if (!mssql_select_db($params['database'], $this->conn)) {
                    throw new Error(Error::selectionBDD($params['database']));
                }*/
            } elseif (MYSQL === $this->driver) {
                if (!mysql_select_db($params['database'], $this->conn)) {
                    throw new Error(Error::selectionBDD($params['database']));
                }
            }
        } else {
            throw new Error(Error::connexionBDD());
        }
    }

    /**
     * Destructeur
     **/
    public function __destruct()
    {
        if (MSSQL === $this->driver) {
            // Ligne commentée car non nécessaire : la connection est fermée automatiquement dès l'arrêt du script
            // @mssql_close($this->conn);
        } elseif (MYSQL === $this->driver) {
            @mysql_close($this->conn);
        }
    }


    /**
     * Requete a la base de donnees
     * @return Tableau à 2 dimensions (enregistrements / champs)
     */
    public function requete($sql)
    {
        $rows = array();

        $duree = microtime(1);
        $memoire = memory_get_usage();
        if (MSSQL === $this->driver) {

            try {
                $stmt = $this->conn->prepare(utf8_decode($sql));
                $stmt->execute();
            } catch (Exception $e) {
                new Error($e->getMessage().'<br/>'.$sql, 99, $e->getFile(), $e->getLine());
            }

        } elseif (MYSQL === $this->driver) {
            $stmt = mysql_query($sql, $this->conn);
        }

        if (!$stmt) {
            throw new Error(Error::erreurRequete($sql));
        }
        if (MSSQL === $this->driver) {
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif (MYSQL === $this->driver) {
            while ($ressource = mysql_fetch_array($stmt)) {
                array_push($rows, $ressource);
            }
            mysql_free_result($stmt);
        }
        $duree -= microtime(1);
        $memoire -= memory_get_usage();
        $stockage_data =   array(
	        'requete' => $sql,
	        'duree' => round(abs($duree), 4),
	        'memoire' => round(abs($memoire) / 1000000, 3),
	        'resultat' => count($rows)
        );
        HistoriqueHelper::logRequete($stockage_data, 'fct_requete');
        Console::enregistrer($stockage_data, LOG_SQL);
        
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
    public function requeteUneLigne($sql)
    {
        $rows = $this->requete($sql);
        if ($rows) {
            return $rows[0];
        } else {
            return array();
        }
    }
    /**
     * @deprecated utiliser requeteUneLigne
     */
     public function requete_une_ligne($sql)
    {
        return $this->requeteUneLigne($sql);
    }

    public function exportQuery($sql)
    {
        if (MSSQL === $this->driver) {
            $stmt = mssql_query($sql, $this->conn);
        } elseif (MYSQL === $this->driver) {
            $stmt = mysql_query($sql, $this->conn);
        }
        if (!$stmt) {
            throw new Error(Error::erreurRequete($sql));
        }
        return $stmt;
    }
    /**
     * @deprecated utiliser exportQuery
     */
    public function export_query($sql)
    {
        return $this->exportQuery($sql);
    }

    public function exportFetchArray($stmt)
    {
        if (MSSQL === $this->driver) {
            return mssql_fetch_array($stmt);
        } elseif (MYSQL === $this->driver) {
            return mysql_fetch_array($stmt);
        }
    }
    /**
     * @deprecated utiliser exportFetchArray
     */
    public function export_fetch_array($stmt)
    {
        return $this->exportFetchArray($stmt);
    }

    /**
     * Requete de mise à jour (update, insert, commit)
     * @return Le nombre de lignes affectées (update, delete) ou bien l'id inséré (insert)
     */
    public function execute($sql, $commentaire = '', $logguer_requete = true)
    {
        //echo($sql);
        $this->nb_execute++;
        //echo($sql."<br><br>");
        if (strtoupper(substr($sql, 0, 7)) == 'UPDATE ') {
            $type_execute = 'UPDATE';
        } elseif (strtoupper(substr($sql, 0, 7)) == 'INSERT ') {
            $type_execute = 'INSERT';
        } elseif (strtoupper(substr($sql, 0, 7)) == 'DELETE ') {
            $type_execute = 'DELETE';
        } elseif (strtoupper(substr($sql, 0, 7)) == 'CREATE ') {
            $type_execute = 'CREATE TABLE';
        } elseif (strtoupper(substr($sql, 0, 12)) == 'ALTER TABLE ') {
            $type_execute = 'ALTER TABLE';
        } elseif (strtoupper(substr($sql, 0, 15)) == 'TRUNCATE TABLE ' && Utilisateur::isCastelis()) {
            $type_execute = 'TRUNCATE TABLE';
        } elseif (strtoupper(substr($sql, 0, 11)) == 'DROP TABLE ' && Utilisateur::isCastelis()) {
            $type_execute = 'DROP TABLE';
        } else {
            throw new Error(Error::typeDeRequeteInconnue());
        }

        if (MSSQL === $this->driver) {
            try {
                $stmt = $this->conn->prepare($sql);

                $duree = microtime(1);
                $memoire = memory_get_usage();

                $stmt->execute();

                $duree -= microtime(1);
                $memoire -= memory_get_usage();
                $stockage_data = array(
                	'requete' => $sql,
	                'duree' => round(abs($duree), 4),
	                'memoire' => round(abs($memoire) / 1000000, 3),
	                'resultat' => $stmt->rowCount()
                );
                HistoriqueHelper::logRequete($stockage_data, 'fct_execute');
                Console::enregistrer($stockage_data, LOG_SQL);
            } catch (Exception $e) {
                new Error($e->getMessage().'<br/>'.$sql, 99, $e->getFile(), $e->getLine());
            }
        } elseif (MYSQL === $this->driver) {
            $stmt = mysql_query($sql, $this->conn);
        }
        if (!$stmt) {
            //echo mysql_error();
            throw new Error(Error::erreurRequete($sql));
        }
        if (MSSQL === $this->driver) {
            if ($_SERVER['SERVER_NAME'] != 'phpunit') {
                switch ($type_execute) {
                    case 'INSERT':
                        // Nouvel id cree
                        $ressource = $this->requeteUneLigne('SELECT @@IDENTITY AS id', '', false);
                        $nouvel_id = round($ressource['id']);
                        return $nouvel_id;
                        break;
                    case 'UPDATE':
                    case 'DELETE':
                        // Nb lignes affectees
                        $ressource = $this->requeteUneLigne('SELECT @@ROWCOUNT AS nb', '', false);
                        $nb_lignes_affectees = round($ressource['nb']);
                        return $nb_lignes_affectees;
                        break;
                    case 'CREATE TABLE':
                    case 'TRUNCATE TABLE':
                    case 'DROP TABLE';
                    case 'ALTER':
                        return true;
                        break;
                    default:
                        echo mysql_info();
                        throw new Error(Error::erreurInconnue());
                }
            }
        } elseif (MYSQL === $this->driver) {
            switch ($type_execute) {
                case 'INSERT':
                    // Nouvel id cree
                    $nouvel_id = mysql_insert_id($this->conn);
                    return $nouvel_id;
                    break;
                case 'UPDATE':
                case 'DELETE':
                case 'TRUNCATE TABLE':
                    // Nb lignes affectees
                    $nb_lignes_affectees = mysql_affected_rows($this->conn);
                    return $nb_lignes_affectees;
                    break;
                case 'CREATE TABLE':
                case 'ALTER TABLE':
                    return true;
                    break;
                default:
                    echo $type_execute;
                    throw new Error(Error::erreurInconnue());
            }
        }
    }

    /**
     * Optimisation table
     *
     * @return Le nombre de lignes affectées (update, delete) ou bien l'id inséré (insert)
     */
    public function optimize ($sql)
    {
        $this->nb_execute++;
        //echo($sql."<br><br>");
        if (!strtoupper(substr($sql, 0, 15)) == 'OPTIMIZE TABLE ') {
            throw new Error(Error::typeDeRequeteInconnue());
        }

        if (MSSQL === $this->driver) {
            $stmt = mssql_query($sql, $this->conn);
        } elseif (MYSQL === $this->driver) {
            $stmt = mysql_query($sql, $this->conn);
        }
        if (!$stmt) {
            //echo mysql_error();
            throw new Error(Error::erreurRequete($sql));
        }
        if (MSSQL === $this->driver) {
            // Nb lignes affectees
            $ressource = $this->requeteUneLigne('SELECT @@ROWCOUNT', '', false);
            $nb_lignes_affectees = round($ressource[0]);
            return $nb_lignes_affectees;
        } elseif (MYSQL === $this->driver) {
            // Nb lignes affectees
            $nb_lignes_affectees = mysql_affected_rows($this->conn);
            return $nb_lignes_affectees;
        }
    }

    public function beginTransaction()
    {
        //mysql_query("SET AUTOCOMMIT = 0; START TRANSACTION;", $this->conn);
        mysql_query("SET AUTOCOMMIT = 0", $this->conn);
        mysql_query("START TRANSACTION", $this->conn);
    }

    public function endTransaction()
    {
        //mysql_query("COMMIT; SET AUTOCOMMIT = 1;", $this->conn);
        mysql_query("COMMIT", $this->conn);
        mysql_query("SET AUTOCOMMIT = 1", $this->conn);
    }

    public function rollback()
    {
        //mysql_query("ROLLBACK; SET AUTOCOMMIT = 1", $this->conn);
        mysql_query("ROLLBACK", $this->conn);
        mysql_query("SET AUTOCOMMIT = 1", $this->conn);
    }
}
