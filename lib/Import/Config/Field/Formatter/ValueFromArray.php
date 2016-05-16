<?php
namespace FMUP\Import\Config\Field\Formatter;

use FMUP\Import\Config\Field\Formatter;

class ValueFromArray implements Formatter
{
    private $array;

    private $hasError = false;

    /**
     * @param string $value
     * @return mixed|null
     */
    public function format($value)
    {
        if (!isset($this->array)) {
            $this->hasError = true;
            throw new \FMUP\Exception\UnexpectedValue("No array defined");
        }
        if (!isset($this->array[$value])) {
            $this->hasError = true;
            return null;
        }
        return $this->array[$value];
    }

    /**
     * @param array $array
     * @return $this
     */
    public function setArray(array $array)
    {
        $this->array = $array;
        return $this;
    }

    public function getErrorMessage($value = null)
    {
        return "La valeur $value n'a pas été trouvée dans le tableau";
    }

    public function hasError()
    {
        return $this->hasError;
    }
}
