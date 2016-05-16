<?php
namespace FMUP\Import\Config\Field\Formatter;

use FMUP\Import\Config\Field\Formatter;

class IdFromObject implements Formatter
{
    private $hasError = false;

    /**
     * @param mixed $value
     * @return int|null
     */
    public function format($value)
    {
        if (!$value instanceof Interfaces\ObjectWithId) {
            $this->hasError = true;
            return null;
        }
        return $value->getId();
    }
    
    public function getErrorMessage($value = null)
    {
        return "La classe n'est pas convertible";
    }

    public function hasError()
    {
        return $this->hasError;
    }

}
