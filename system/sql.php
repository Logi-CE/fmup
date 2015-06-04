<?php
class Sql
{
    /**
     * Protège des injections SQL
     * @param {String} la chaîne à sécuriser
     *
     */
    public static function sanitize($value)
    {
        $temp = Config::parametresConnexionDb();

        switch ($temp['driver']) {
            case 'mysql':
                //return mysql_real_escape_string($value);
                return addslashes($value);
            case 'mssql':
                return str_replace('\'', '\'\'', $value);
        }
    }
    /**
     * Protège des injections SQL (pour les requètes)
     * @param {String} la chaîne à sécuriser
     **/
    public static function secure($value)
    {
        return "'".Sql::sanitize($value)."'";
    }
    /**
     * Protège des injections SQL pour les integers
     * @param {String} la chaîne à sécuriser
     **/
    public static function secureId($value)
    {
        if (Is::id($value)) {
            return $value;
        } else {
            return "null"; // entre guillements pour que ça devienne une requète SQL
        }
    }
    public static function secureListeId($value)
    {
        if ($value) {
            return $value;
        } else {
            return "0"; // entre guillements pour que ça devienne une requète SQL
        }
    }

    /**
     * Protège des injections SQL pour les integers
     * @param {String} la chaîne à sécuriser
     **/
    public static function secureInteger($value)
    {
        $value = strtr($value, ' ', '');
        if (Is::integer($value)) {
            return $value;
        } else {
            return "null"; // entre guillements pour que ça devienne une requète SQL
        }
    }
    /**
     * Protège des injections SQL pour les integers
     * @param {String} la chaîne à sécuriser
     **/
    public static function secureBoolean($value)
    {
        if (Is::integer($value)) {
            if ($value) {
                return "1";
            } else {
                return "0";
            }
        } else {
            return "0"; // entre guillements pour que ça devienne une requète SQL
        }
    }

    /**
     * Protège des injections décimales SQL en remplaçant les "," par des "."
     * @param {Decimal} le Décimal à sécuriser
     **/
    public static function secureDecimal($value)
    {
        $value = str_replace(' ', '', $value);
        if (Is::decimal($value)) {
            return str_replace(",", ".", $value);
        } else {
            return "null";
        }
    }
    /**
     * Protège des injections de date SQL en remplaçant les "" par des NULL
     * @param {date} lea date à sécuriser
     **/
    public static function secureDate($value)
    {
        if (Is::dateTime($value) || Is::dateTimeUk($value)) {
            return "'".$value."'";
        } else {
            return "null";
        }
    }
    /**
     * Protège des injections SQL (pour les requètes)
     * @param {Array} le tableau à sécuriser
     **/
    public static function secureArray($values)
    {
        array_map(array('Sql', 'sanitize'), $values);
        return "'".implode("', '", $values)."'";
    }

    public static function replaceXFields($tab, $class = null, $option = array())
    {
        if ($class) {
            $fields = call_user_func(array($class, 'xFields'), $option);
            $orig_fields = array_keys($fields);
            $dest_fields = array_values($fields);
            foreach ($tab as $key => $value) {
                $tab[$key] = preg_replace($orig_fields, $dest_fields, trim($value));
            }
        }

        return $tab;
    }

    public static function parseOrder($order, $class = '')
    {
        $order_parse = '';

        if ($order) {
            $tab1 = explode(', ', $order);
            $tab2 = self::replaceXFields($tab1, $class);
            $order_parse = implode(',', $tab2);
        }

        return $order_parse;
    }

    /**
     * Convertit un tableau de conditions en un WHERE conditions
     * Le deuxième paramètre permet de faire un HAVING à la place
     **/

    public static function parseWhere($where, $having = false, $class = null, $option = array())
    {
        $where = self::replaceXFields($where, $class, $option);

        if (! is_array($where)) {
            throw new Error("Erreur à l'utilisation de sqlParseWhere : tableau attendu. Reçu : ".var_dump($where));
        }

        $where = array_filter($where, function($i) { return $i <> "";});
        if ($where == array()) {
            return "";
        } else {
            if ($having) {
                $result = " HAVING ";
            } else {
                $result = " WHERE ";
            }
            foreach ($where as $condition) {
                if ($condition != '') {
                    $result .= '('.$condition.')' . ' AND '."\n";
                }
            }
            // suppression du dernier AND
            $result = substr($result, 0, -5);
            return $result;
        }
    }

/* *********
 * Filtres *
 ********* */
    /**
     * Cette fonction crée un tableau de conditions LIKE à partir d'un tableau
     **/
    public static function conditionsFromArray($params)
    {
        $where = array();
        Console::enregistrer($params);
        foreach ($params as $champ => $valeur) {
            if (0 === strpos($champ, 'id_') || $champ == 'id') {
                if ($valeur != '') {
                    $where[$champ] = "$champ = ".Sql::secureId($valeur);
                }
            } elseif (0 === strpos($champ, "date_")) {
                //$where[$champ] = RequeteHelper::convertFiltreDate($champ, $valeur);
                $where[$champ] = " CONVERT(VARCHAR, ".$champ.", 103) LIKE '%".Sql::sanitize(trim($valeur))."%' " ;
            } elseif (0 < strpos($champ, "chrono") && $valeur) {
                try {
                    $valeur = intval($valeur);
                } catch (Exception $e) {
                    $valeur = $valeur;
                }
                if (0 === strpos($champ, 'equal_')) {
                    $champ = substr($champ, 6);
                    $where[$champ] = "$champ LIKE '".Sql::sanitize(trim($valeur))."'";
                } else {
                    $where[$champ] = "$champ LIKE '".Sql::sanitize(trim($valeur))."%'";
                }
            } else {
                if ($valeur == "NULL") {
                    $where[$champ] = "$champ IS NULL";
                } elseif ($valeur == "IS NOT NULL") {
                    $where[$champ] = "$champ IS NOT NULL";
                } elseif ($valeur != '') {
                    if (0 === strpos($champ, 'equal_')) {
                        $champ = substr($champ, 6);
                        $where[$champ] = "$champ = '".Sql::sanitize(trim($valeur))."'";
                    } else {
                        $where[$champ] = "$champ LIKE '%".Sql::sanitize(trim($valeur))."%'";
                    }
                }
            }
        }
        return $where;
    }
}
