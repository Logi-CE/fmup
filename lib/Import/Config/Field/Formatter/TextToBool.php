<?php
namespace FMUP\Import\Config\Field\Formatter;

use FMUP\Import\Config\Field\Formatter;

class TextToBool implements Formatter
{

    private $has_error = false;

    public function format($value)
    {
        if ($value == "") {
            $this->has_error = true;
            return "";
        } else {
            if (strtolower($value) == "oui") {
                return true;
            } elseif (strtolower($value) == "non") {
                return false;
            }
        }
    }

    public function getErrorMessage($value = null)
    {
        return "La valeur  " . $value . "n'est pas convertible";
    }

    public function hasError()
    {
        return $this->has_error;
    }
}
