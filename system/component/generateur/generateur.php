<?php
class Generateur
{
    protected $db;
    protected $params_connexion = array();
    
    public function __construct()
    {
        $this->params_connexion = Config::parametresConnexionDb();
        switch ($this->params_connexion['driver']) {
            case 'mysql':
                $this->db = DbConnectionMysql::getInstance($this->params_connexion);
                break;
            case 'mssql':
                $this->db = new DbConnectionMssql($this->params_connexion);
                break;
        }
    }
    
    public function getTables ()
    {
        if ($this->params_connexion['driver'] == 'mysql') {
            $SQL = "SHOW TABLES;";
        } elseif ($this->params_connexion['driver'] == 'mssql') {
            // On emploie l'alias '0' pour la boucle suivante afin de ne pas dupliquer le code
            $SQL = "SELECT DISTINCT TABLE_NAME AS '0' FROM INFORMATION_SCHEMA.TABLES;";
        }
        $tableau_log = array();
        $tableau_table = array();
        if ($this->db) {
            $tables = $this->db->requete($SQL);
            foreach ( $tables as $table ) {
                $table = array_shift($table);
                if (strtoupper(substr($table, 0, 5)) == 'LOG__') {
                    $tableau_log[] = substr($table, 5);
                }
                if ( (strtoupper(substr($table[0], 0, 5)) != 'LOG__')
                    && (strtoupper(substr($table, 0, 8)) != 'DROITS__')
                    && (strtoupper(substr($table, 0, 14)) != 'HISTORISATIONS')) {
                    $tableau_table[] = $table;
                }
            }
        }
        return array('tableau_table' => $tableau_table, 'tableau_log' => $tableau_log);
    }
    
    public function getColonnesTable ($nom_table)
    {
        $sql = "SELECT COLUMN_NAME AS nom, COLUMN_TYPE AS type
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_NAME='".$nom_table."'
                    AND TABLE_SCHEMA = '".$this->params_connexion['database']."'";
        $champs_tableau = $this->db->requete($sql);
        $champs = array();
        foreach ($champs_tableau as $champ) {
            array_push($champs, $champ);
        }
        return $champs;
    }
    
    public function supprimerModeles ()
    {
        if ($dossier = opendir(BASE_PATH.'/application/model/base')) {
            while (false !== ($fichier = readdir($dossier))) {
                if ($fichier != '.' && $fichier != '..') {
                    unlink(BASE_PATH.'/application/model/base/'.$fichier);
                }
                    
            }
            closedir($dossier);
        }
    }
    
    public function genererControleur ($nom_table, $chemin_vues, $champs)
    {
        $nom_modele = String::toCamlCase($nom_table);
        $nom_classe = String::toCamlCase('Ctrl_'.$nom_table);
        $nom_fichier_court = String::to_Case($nom_table);
        $nom_fichier = "ctrl_".String::to_Case($nom_fichier_court);
        $objet = str_replace('_', ' ', $nom_table);
        
        if (!file_exists(BASE_PATH."/public/_admin_/temp/controller/")) mkdir(BASE_PATH."/public/_admin_/temp/controller/", 0777, true);
        if (!file_exists(BASE_PATH."/public/_admin_/temp/view/$chemin_vues")) {
            mkdir(BASE_PATH."/public/_admin_/temp/view/$chemin_vues", 0777, true);
        }
        
        ob_start();
        echo "<?php\n";
        include './template_controleur.php';
        $buffer = ob_get_clean();
        
        echo '<pre class="code">'.str_replace('<', '&lt;', $buffer).'</pre>';
        
        file_put_contents(BASE_PATH."/public/_admin_/temp/controller/".$nom_fichier.".php", $buffer);
        
        ob_start();
        include './template_vue_filtrer.php';
        $buffer = ob_get_clean();
        
        echo '<pre class="code">'.str_replace('<', '&lt;', $buffer).'</pre>';
        
        file_put_contents(BASE_PATH."/public/_admin_/temp/view/$chemin_vues/filtrer.phtml", $buffer);
        
        ob_start();
        include './template_vue_editer.php';
        $buffer = ob_get_clean();
        
        echo '<pre class="code">'.str_replace('<', '&lt;', $buffer).'</pre>';
        
        file_put_contents(BASE_PATH."/public/_admin_/temp/view/$chemin_vues/editer.php", $buffer);
    }
    
    public function genererModele ($nom_table, $nom_classe, $logguer = false)
    {
        $params_connexion = Config::parametresConnexionDb();
        $table = Sql::sanitize($nom_table);
        $class = Sql::sanitize($nom_classe);
        $variable = String::to_Case($nom_classe);
        $suppression_physique = true;
        $primary_key = false;
        $user_action_exists = false;
        $date_action_exists = false;
        $action_exists = false;
        $id_objet_exists = false;
        $id_histo_exists = false;
        $libelle_histo_exists = false;
        $contenu_histo_exists = false;
        
        if ($logguer) {
			//Si on demande des logs on crée un table de logs
            if ($params_connexion['driver'] == 'mysql') {
                $this->db->execute("CREATE TABLE IF NOT EXISTS log__$table SELECT * FROM $table WHERE 1=0");
                $champs_logs = $this->db->requete("SHOW COLUMNS FROM log__$table");
            } elseif ($params_connexion['driver'] == 'mssql') {
                $this->db->execute("  IF NOT EXISTS (SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'log__$table')
                                BEGIN
                                    CREATE TABLE log__$table SELECT * FROM $table WHERE 1=0
                                END");
                $champs_logs = $this->db->requete("SELECT  COLUMN_NAME as Field
                                                    , DATA_TYPE as Type
                                            FROM INFORMATION_SCHEMA.COLUMNS
                                            WHERE TABLE_NAME = 'log__$table'");
            }
			
			$id_log_exists = false;
			foreach ( $champs_logs as $champ ) {
				if ($champ["Key"] == 'PRI') $primary_key = true;
				if ($champ["Field"] == "id_$table") $id_log_exists = true;
				if ($champ["Field"] == "id_utilisateur_log") $user_action_exists = true;
				if ($champ["Field"] == "date_action_log") $date_action_exists = true;
				if ($champ["Field"] == "action_log") $action_exists = true;
				if ($champ["Field"] == "id_objet_log") $id_objet_exists = true;
				if ($champ["Field"] == "id_historisation") $id_histo_exists = true;
				if ($champ["Field"] == "libelle_historisation") $libelle_histo_exists = true;
				if ($champ["Field"] == "contenu_log") $contenu_histo_exists = true;
			}
			if (count ($champs_logs) > 0) {
				if (!$primary_key) {
					$this->db->execute('ALTER TABLE `log__'.$table.'` ADD PRIMARY KEY(`id`)');
					$this->db->execute('ALTER TABLE `log__'.$table.'` CHANGE `id` `id` INT( 11 ) NOT null AUTO_INCREMENT');
				}
				if (!$id_objet_exists) $this->db->execute("ALTER TABLE `log__$table` ADD `id_objet_log` INT NOT null AFTER `id`");
				if (!$id_histo_exists) $this->db->execute("ALTER TABLE `log__$table` ADD `id_historisation` INT NOT null AFTER `id_objet_log`");
				if (!$libelle_histo_exists) $this->db->execute("ALTER TABLE `log__$table` ADD `libelle_historisation` TEXT NOT null AFTER `id_historisation`");
				if (!$contenu_histo_exists) $this->db->execute("ALTER TABLE `log__$table` ADD `contenu_log` TEXT NOT null AFTER `libelle_historisation`");
				if (!$id_log_exists) $this->db->execute("ALTER TABLE `log__$table` ADD `id_$table` INT NOT null AFTER `contenu_log`");
				if (!$user_action_exists) $this->db->execute("ALTER TABLE `log__$table` ADD `id_utilisateur_log` INT NOT null");
				if (!$date_action_exists) $this->db->execute("ALTER TABLE `log__$table` ADD `date_action_log` DATETIME NOT null");
				if (!$action_exists) $this->db->execute("ALTER TABLE `log__$table` ADD `action_log` VARCHAR( 10 ) NOT null ");
			}
		}
        $champs = array();
        $tables_liees = array();
        if ($params_connexion['driver'] == 'mysql') {
            $champs_tableau = $this->db->requete("SHOW COLUMNS FROM $table");
            foreach ($champs_tableau as $index => $champ) {
                list($type) = explode('(', $champ["Type"]);
                if (in_array($type, array('int', 'tinyint', 'smallint', 'mediumint', 'bigint'))) {
                    if (0 === strpos($champ["Field"], 'id_')) {
                        $champs_tableau[$index]['type_application'] = 'id';
                    } else {
                        $champs_tableau[$index]['type_application'] = 'integer';
                    }
                } elseif (in_array($type, array('date', 'datetime', 'timestamp'))) {
                    $champs_tableau[$index]['type_application'] = 'date';
                } elseif (in_array($type, array('float', 'double', 'decimal'))) {
                    $champs_tableau[$index]['type_application'] = 'decimal';
                } else {
                    $champs_tableau[$index]['type_application'] = 'string';
                }
                
                if (!is_integer($champ)) {
                    array_push($champs, $champ["Field"]);
                }
                if ($champ["Field"] == 'supprime') {
                    $suppression_physique = false;
                }
            }
            $tables_temp = $this->db->requete("SHOW TABLES");
            foreach ($tables_temp as $numero => $nom_table) {
                $nom_table = array_shift($nom_table);
                if ((strtoupper(substr($nom_table, 0, 5)) != 'LOG__')) {
                    $champs_temp = $this->db->requete("SHOW COLUMNS FROM $nom_table");
                    foreach ($champs_temp as $champ) {
                        $champ = array_shift($champ);
                        if ($champ == "id_".$table) {
                            $tables_liees[] = $nom_table;
                        }
                    }
                }
            }
        } else {
            if ($params_connexion['driver'] == 'mssql') {
                $SQL = "SELECT  COLUMN_NAME as Field
                                , DATA_TYPE as Type
                                , COLUMN_DEFAULT as 'Default'
                                , IS_nullABLE as 'Null'
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_NAME = '$table'";
                $champs_tableau = $this->db->requete($SQL);
                foreach ($champs_tableau as $index => $champ) {
                    list($type) = explode('(', $champ["Type"]);
                    if (in_array($type, array('int', 'tinyint', 'smallint', 'bigint'))) {
                        if (0 === strpos($champ["Field"], 'id_')) {
                            $champs_tableau[$index]['type_application'] = 'id';
                        } else {
                            $champs_tableau[$index]['type_application'] = 'integer';
                        }
                    } elseif (in_array($type, array('date', 'datetime', 'timestamp'))) {
                        $champs_tableau[$index]['type_application'] = 'date';
                    } elseif (in_array($type, array('float', 'double', 'real', 'decimal', 'numeric'))) {
                        $champs_tableau[$index]['type_application'] = 'decimal';
                    } else {
                        $champs_tableau[$index]['type_application'] = 'string';
                    }
                    
                    if (!is_integer($champ)) {
                        array_push($champs, $champ["Field"]);
                    }
                    if ($champ["Field"] == 'supprime') {
                        $suppression_physique = false;
                    }
                }
                
                $tables_temp = $this->db->requete("SELECT DISTINCT TABLE_NAME AS '0' FROM INFORMATION_SCHEMA.TABLES");
                foreach ($tables_temp as $numero => $nom_table) {
                    if ((strtoupper(substr($nom_table[0], 0, 5)) != 'LOG__')) {
                        $champs_temp = $this->db->requete("SELECT  COLUMN_NAME as Field
                                                            , DATA_TYPE as Type
                                                            , COLUMN_DEFAULT as 'Default'
                                                            , IS_nullABLE as 'Null'
                                                    FROM INFORMATION_SCHEMA.COLUMNS
                                                    WHERE TABLE_NAME = '$nom_table[0]'");
                        foreach ($champs_temp as $champ) {
                            if ($champ['Field'] == "id_".$table) {
                                $tables_liees[] = $nom_table[0];
                            }
                        }
                    }
                }
            }
        }
        
        ob_start();
        echo "<?php\n";
        include './template_modele.php';
        $buffer = ob_get_clean();
        
        echo '<pre class="code">'.str_replace('<', '&lt;', $buffer).'</pre>';
        file_put_contents(BASE_PATH."/application/model/base/base_".String::to_Case($class).".php", $buffer);
        
        if(!is_file(BASE_PATH.'/application/model/'.String::to_Case($class).'.php')) {
            file_put_contents(BASE_PATH.'/application/model/'.String::to_Case($class).'.php', '<?php
require_once dirname(__FILE__).\'/base/base_'.String::to_Case($class).'.php\';
/**
 * Modèle '.String::toCamlCase($class).'
 * Classe créée le '.Date::today('FR', true).'
 */
class '.String::toCamlCase($class).' extends Base'.String::toCamlCase($class).'
{

}');
        }
    }
}