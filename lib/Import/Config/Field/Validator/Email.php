<?php
namespace FMUP\Import\Config\Field\Validator;

use FMUP\Import\Config\Field\Validator;

class Email implements Validator
{

    public function validate($value)
    {
        $valid = true;
        if (!\Is::courriel($value)) {
            $valid = false;
        }
        return $valid;
    }

    public function getErrorMessage()
    {
        return "Le champ reçu n'est pas un email valide";
    }
}
