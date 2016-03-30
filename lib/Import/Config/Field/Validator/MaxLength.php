<?php
namespace FMUP\Import\Config\Field\Validator;

use FMUP\Import\Config\Field\Validator;

class MaxLength implements Validator
{
    private $length;

    public function __construct($length)
    {
        $this->length = $length;
    }

    public function validate($value)
    {
        return (bool) strlen($value) <= $this->length;
    }

    public function getErrorMessage()
    {
        return "Le champ reçu est trop grand";
    }
}
