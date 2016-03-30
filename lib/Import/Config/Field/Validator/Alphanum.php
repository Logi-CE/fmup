<?php
namespace FMUP\Import\Config\Field\Validator;

use FMUP\Import\Config\Field\Validator;

class Alphanum implements Validator
{

    public function validate($value)
    {
        return (bool) \Is::alphaNumerique($value);
    }

    public function getErrorMessage()
    {
        return "Le champ reçu n'est pas";
    }
}
