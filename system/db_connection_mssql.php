<?php
if (!defined('MSSQL')) define('MSSQL', 'mssql');
if (!defined('MYSQL')) define('MYSQL', 'mysql');

/**
 * Classe de connexion à une base de données
 * @version 1.0
 * @deprecated use \FMUP\Db instead
 * @see \FMUP\Db
 */
class DbConnectionMssql
{
    protected $conn; // la connexion
    protected $driver; // le moteur (MYSQL, MSSQL, ...)
    protected $charset;

    /**
     * Constructeur
     **/
    public function __construct($params)
    {
        if ((isset($params['host'], $params['login'], $params['password'], $params['database'], $params['driver']))) {
            // Driver
            $this->driver = $params['driver'];
            if (isset($params['charset'])) {
                $this->charset = $params['charset'];
            } else {
                $this->charset = "utf8";
            }

            // Connexion à la base de données
            if (MSSQL === $this->driver) {
                $this->conn = new PDO('odbc:Driver={SQL Server};Server={'.$params['host'].'};Database={'.$params['database'].'};charset=UTF-8;', $params['login'], $params['password']);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // les erreurs lanceront des exceptions
                $this->conn->setAttribute(PDO::ATTR_TIMEOUT, 10.0);
            }
            if (!$this->conn) {
                throw new \FMUP\Db\Exception('Erreur de connexion à la base de données.');
            } else {
                Console::enregistrer($params['host'].'/'.$params['database'].' ('.$params['driver'].')', LOG_CONNEXION);
            }
        } else {
            throw new \FMUP\Db\Exception('Erreur de connexion à la base de données.');
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

        $stmt = $this->conn->prepare(utf8_decode($sql));
        $stmt->execute();

        if (!$stmt) {
            throw new \FMUP\Db\Exception("Erreur de requète : " . $sql);
        }
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    private function utf8EncodeFilter(&$item, $index)
    {
        $item = utf8_encode($item);
    }

    public function requeteUtf8($sql)
    {
        $resultat = self::requete($sql);
        array_walk_recursive($resultat, array($this, 'utf8EncodeFilter'));
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

    public function exportQuery($sql)
    {
        $rows = array();
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new \FMUP\Db\EXception("Erreur de requète : ".$sql);
        }

        $duree = microtime(1);
        $memoire = memory_get_usage();

        $stmt->execute();

        $duree -= microtime(1);
        $memoire -= memory_get_usage();
        Console::enregistrer(array('requete' => $sql, 'duree' => round(abs($duree), 4), 'memoire' => round(abs($memoire) / 1000000, 3), 'resultat' => $stmt->rowCount()), LOG_SQL);

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
            throw new \FMUP\Db\Exception('Erreur : type de requète inconnue.');
        }

        $retour = false;
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new \FMUP\Db\Exception("Erreur de requète : ".$sql);
        }

        $duree = microtime(1);
        $memoire = memory_get_usage();

        $stmt->execute();
        if (!isset($_SERVER['SERVER_NAME']) || $_SERVER['SERVER_NAME'] != 'phpunit') {
            switch ($type_execute) {
                case 'INSERT':
                    // Nouvel id cree
                    $ressource = $this->requeteUneLigne('SELECT @@IDENTITY AS id', '', false);
                    $retour = round($ressource['id']);
                    break;
                case 'UPDATE':
                case 'DELETE':
                    // Nb lignes affectees
                    $ressource = $this->requeteUneLigne('SELECT @@ROWCOUNT AS nb', '', false);
                    $retour = round($ressource['nb']);
                    break;
                case 'CREATE TABLE':
                case 'TRUNCATE TABLE':
                case 'DROP TABLE';
                case 'ALTER':
                    $retour = true;
                    break;
                default:
                    throw new \FMUP\Db\Exception('Erreur : type de requète inconnue.');
            }
        }
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
        return $retour;
    }

    /**
     * Optimisation table
     *
     * @return Le nombre de lignes affectées (update, delete) ou bien l'id inséré (insert)
     */
    public function optimize ($sql)
    {
        $this->nb_execute++;
        if (!strtoupper(substr($sql, 0, 15)) == 'OPTIMIZE TABLE ') {
            throw new \FMUP\Db\Exception('Erreur : type de requète inconnue.');
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        if (!$stmt) {
            throw new \FMUP\Db\Exception("Erreur de requète : " . $sql);
        }
        // Nb lignes affectees
        $ressource = $this->requeteUneLigne('SELECT @@ROWCOUNT', '', false);
        $nb_lignes_affectees = round($ressource[0]);
        return $nb_lignes_affectees;
    }

    public function beginTrans()
    {
        $this->conn->beginTransaction();
    }

    public function commitTrans()
    {
        $this->conn->commit();
    }

    public function rollbackTrans()
    {
        $this->conn->rollBack();
    }
}
