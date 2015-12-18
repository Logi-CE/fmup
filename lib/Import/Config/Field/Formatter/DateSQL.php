<?php
namespace FMUP\Import\Config\Field\Formatter;

use FMUP\Import\Config\Field\Formatter;

class DateSQL implements Formatter
{

    private $has_error = false;

    public function format($value)
    {
        if ($value == "") {
            $this->has_error = true;
            return "Champ vide";
        } else {
            $result = $this->toDate($value);
            if ($result) {
                return $result;
            } else {
                $this->has_error = true;
                return $value;
            }
        }
    }
    protected function toDate($value)
    {
        $date = \DateTime::createFromFormat('d/m/Y', $value);
        if (!$date) {
            $date = \DateTime::createFromFormat('Y-m-d', $value);
        }
        return $date->format('Y-m-d H:i:s');
    }

    public function getErrorMessage($value = null)
    {
        return "La valeur $value n'est pas convertible";
    }

    public function hasError()
    {
        return $this->has_error;
    }
}
