<?php
namespace FMUP\Import\Config\Field\Validator;

use FMUP\Import\Config\Field\Validator;

class Date implements Validator
{

    public function validate($value)
    {
        $valid = true;
        if (!\Is::date($value) && !\Is::dateUk($value)) {
            $valid = false;
        }
        return $valid;
    }

    public function getErrorMessage()
    {
        return "Le champ reçu n'est pas une date valide";
    }
}
