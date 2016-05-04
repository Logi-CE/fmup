<?php
namespace FMUP\Import\Config\Field\Validator;

use FMUP\Import\Config\Field\Validator;

class Id implements Validator
{

    public function validate($value)
    {
        return (bool)\Is::id($value);
    }

    public function getErrorMessage()
    {
        return "Le champ reçu n'est pas un ID";
    }
}
