<?php
namespace FMUP\Import\Config\Field\Validator;

use FMUP\Import\Config\Field\Validator;

class Date implements Validator
{
    private $empty;

    public function __construct($empty = false)
    {
        $this->setCanEmpty($empty);
    }

    public function setCanEmpty($empty)
    {
        $this->empty = $empty;
        return $this;
    }

    public function canEmpty()
    {
        return $this->empty;
    }

    public function validate($value)
    {
        $valid = true;
        if (!($this->canEmpty() && $value == '') && !\Is::date($value) && !\Is::dateUk($value)) {
            $valid = false;
        }
        return $valid;
    }

    public function getErrorMessage()
    {
        return "Le champ reçu n'est pas une date valide";
    }
}
