<?php
namespace FMUP\Import\Config\Field\Formatter;

use FMUP\Import\Config\Field\Formatter;

class TextToBool implements Formatter
{
    private $hasError = false;

    /**
     * @param string $value
     * @return bool|string
     */
    public function format($value)
    {
        if ($value == "") {
            $this->hasError = true;
            return "";
        } else {
            if (strtolower($value) == "oui") {
                return true;
            } elseif (strtolower($value) == "non") {
                return false;
            }
        }
        return "";
    }

    public function getErrorMessage($value = null)
    {
        return "La valeur $value n'est pas convertible";
    }

    public function hasError()
    {
        return $this->hasError;
    }
}
