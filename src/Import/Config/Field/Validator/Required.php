<?php
namespace FMUP\Import\Config\Field\Validator;

use FMUP\Import\Config\Field\Validator;

class Required implements Validator
{
    public function validate($value)
    {
        return ($value !== false && $value !== null && $value !== "");
    }

    public function getErrorMessage()
    {
        return "Ce champ est obligatoire mais aucune donnée n'a été reçue";
    }
}
