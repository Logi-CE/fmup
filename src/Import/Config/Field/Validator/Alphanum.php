<?php
namespace FMUP\Import\Config\Field\Validator;

use FMUP\Import\Config\Field\Validator;

class Alphanum implements Validator
{

    /**
     * @param string $value
     * @return bool
     */
    public function validate($value)
    {
        return (bool)preg_match('~^\w*$~', $value);
    }

    public function getErrorMessage()
    {
        return "Le champ reçu n'est pas alphanumérique";
    }
}
