<?php
namespace FMUP\Import\Config\Field\Validator;

use FMUP\Import\Config\Field\Validator;

class HalfOrInteger implements Validator
{
    public function validate($value)
    {
        $valid = true;
        if ((\Is::integer($value) === false) && (\Is::half($value) === false)) {
            $valid = false;
        }
        return $valid;
    }

    public function getErrorMessage()
    {
        return "Le champ reçu n'est un nombre entier où la décimale ne correspond pas à un demi de un";
    }
}
