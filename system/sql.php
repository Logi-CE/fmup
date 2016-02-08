<?php

/**
 * Classe fornissant des fonctions de protection des injections SQL et d'autres fonctions liées aux requetes
 * @version 1.0
 * @deprecated use \FMUP\Db instead
 */
class Sql
{
    /**
     * Protège des injections SQL
     * @param {String} la chaîne à sécuriser
     *
     */
    public static function sanitize($value)
    {
        $value = preg_replace('@<script[^>]*?>.*?</script>@si', '[disabled]', $value);
        return str_replace('\'', '\'\'', $value);
    }

    /**
     * Protège des injections SQL (pour les requètes)
     * @param {String} la chaîne à sécuriser
     **/
    public static function secure($value)
    {
        return "'" . Sql::sanitize($value) . "'";
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
            $values = explode(',', $value);
            $values = self::secureArray($values);
            return implode(',', $values);
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
     * Protège des injections de date SQL en remplaçant les "" par des null
     * @param {date} lea date à sécuriser
     **/
    public static function secureDate($value)
    {
        if ($value instanceof \DateTime) {
            return '"' . $value->format('Y-m-d') . '"';
        }
        if (Is::dateTime($value) || Is::dateTimeUk($value)) {
            return "'" . $value . "'";
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
        return array_map(array('Sql', 'secure'), $values);
    }

    public static function replaceXJoins($tabs, $orig_join, &$join = array())
    {
        foreach ($tabs as $tab) {
            if (!isset($join[$tab])) {
                if (isset($orig_join[$tab]['dep'])) {
                    self::replaceXJoins($orig_join[$tab]['dep'], $orig_join, $join);
                }
                if (isset($orig_join[$tab])) {
                    $join[$tab] = $orig_join[$tab]['join'];
                }
            }
        }
    }

    public static function replaceXFields($tab, $class = null, $option = array(), &$join = array())
    {
        if ($class) {
            $fields = call_user_func(array($class, 'xFields'), $option);
            $orig_fields = array_keys($fields);
            $dest_fields = array_values($fields);
            if (isset($option['x_joins'])) {
                $orig_join = call_user_func(array($class, 'xJoins'), $option);
            }
            foreach ($tab as $key => $value) {
                $tab[$key] = preg_replace($orig_fields, $dest_fields, trim($value));
                if (!empty($orig_join)) {
                    preg_match_all("/([[:alpha:]_]+)\./", $tab[$key], $out);
                    self::replaceXJoins($out[1], $orig_join, $join);
                }
            }
        }

        return $tab;
    }

    private static function filterWhere($i)
    {
        return $i <> "";
    }

    /**
     * Convertit un tableau de conditions en un WHERE conditions
     * Le deuxième paramètre permet de faire un HAVING à la place
     * @uses self::filterWhere
     */
    public static function parseWhere($where, $having = false, $class = null, $option = array())
    {
        $where = self::replaceXFields($where, $class, $option);

        if (!is_array($where)) {
            throw new \FMUP\Exception(
                "Erreur à l'utilisation de sqlParseWhere : tableau attendu. Reçu : " . serialize($where)
            );
        }

        $where = array_filter($where, array('\Sql', 'filterWhere'));
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
                    $result .= '(' . $condition . ') ' . "\n" . 'AND ';
                }
            }
            // suppression du dernier AND
            $result = substr($result, 0, -5);
            return $result;
        }
    }

    /*     * ********
     * Filtres *
     * ******** */

    /**
     * Cette fonction crée un tableau de conditions LIKE à partir d'un tableau
     */
    public static function conditionsFromArray($params)
    {
        $where = array();
        foreach ($params as $champ => $valeur) {
            if (0 === strpos($champ, 'id_') || $champ == 'id') {
                if ($valeur != '') {
                    $where[$champ] = "$champ = " . Sql::secureId($valeur);
                }
            } elseif (0 === strpos($champ, "date_")) {
                $where[$champ] = " CONVERT(VARCHAR, " . $champ . ", 103) LIKE '%"
                    . Sql::sanitize(trim($valeur)) . "%' ";
            } elseif (0 < strpos($champ, "chrono") && $valeur) {
                try {
                    $valeur = intval($valeur);
                } catch (Exception $e) {
                    $valeur = $valeur;
                }
                if (0 === strpos($champ, 'equal_')) {
                    $champ = substr($champ, 6);
                    $where[$champ] = "$champ LIKE '" . Sql::sanitize(trim($valeur)) . "'";
                } else {
                    $where[$champ] = "$champ LIKE '" . Sql::sanitize(trim($valeur)) . "%'";
                }
            } else {
                if ($valeur == "null") {
                    $where[$champ] = "$champ IS null";
                } elseif ($valeur == "IS NOT null") {
                    $where[$champ] = "$champ IS NOT null";
                } elseif ($valeur != '') {
                    if (0 === strpos($champ, 'equal_')) {
                        $champ = substr($champ, 6);
                        $where[$champ] = "$champ = '" . Sql::sanitize(trim($valeur)) . "'";
                    } else {
                        $where[$champ] = "$champ LIKE '%" . Sql::sanitize(trim($valeur)) . "%'";
                    }
                }
            }
        }
        return $where;
    }
}
