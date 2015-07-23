<?php
namespace FMUP\Import\Config\Field\Validator;

use FMUP\Import\Config\Field\Field;

class LongueurMax implements Validator
{

    private $longueur;

    public function __construct($longueur)
    {
        $this->longueur = $longueur;
    }

    public function validate($value)
    {
        $valid = true;
        if (strlen($value) > $this->longueur) {
            $valid = false;
        }
        return $valid;
    }

    public function getErrorMessage()
    {
        return "Le champ reçu est trop grand";
    }
}