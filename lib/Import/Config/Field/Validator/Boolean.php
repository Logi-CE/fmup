<?php
namespace FMUP\Import\Config\Field\Validator;

use FMUP\Import\Config\Field\Validator;

class Boolean implements Validator
{

    public function validate($value)
    {
        return (bool)\Is::booleen($value);
    }

    public function getErrorMessage()
    {
        return "Le champ reçu n'est pas un boolean";
    }
}
