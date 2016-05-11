<?php
namespace FMUP\Import\Config\Field\Validator;

use FMUP\Import\Config\Field\Validator;

class Boolean implements Validator
{
    /**
     * @param mixed $value
     * @return bool
     */
    public function validate($value)
    {
        return (bool)($value === true || $value === false);
    }

    public function getErrorMessage()
    {
        return "Le champ reçu n'est pas un boolean";
    }
}
