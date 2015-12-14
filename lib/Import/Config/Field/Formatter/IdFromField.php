<?php
namespace FMUP\Import\Config\Field\Formatter;

use FMUP\Import\Config\Field\Formatter;

class IdFromField implements Formatter
{

    private $champ_origine;

    private $table_origine;

    private $has_error = false;

    public function __construct($champ_origine, $table_origine)
    {
        $this->champ_origine = $champ_origine;
        $this->table_origine = $table_origine;
    }

    /**
     * @param string $value
     * @return string
     */
    public function format($value)
    {
        if ($value == "") {
            $this->has_error = true;
            return "";
        } else {
            $sql = "
            SELECT id
            FROM " . $this->table_origine . "
            WHERE " . $this->champ_origine . " LIKE '%" . $value . "%'    
            ";
            $db = \Model::getDb();
            $result = $db->fetchAll($sql);
            if ($result) {
                return $result[0]['id'];
            } else {
                $this->has_error = true;
                return $value;
            }
        }
    }

    public function getErrorMessage($value = null)
    {
        return "Aucune correspondance n'a été trouvé pour le champ : '" . $this->champ_origine . "' de la table : '"
            . $this->table_origine . "' pour la valeur : '" . $value . "'";
    }

    public function hasError()
    {
        return $this->has_error;
    }
}
