<?php
namespace FMUP\Import\Config\Field\Validator;

use FMUP\Import\Config\Field\Validator;

class Telephone implements Validator
{
    private $empty;

    public function __construct($empty = false)
    {
        $this->setCanEmpty($empty);
    }

    public function setCanEmpty($empty = false)
    {
        $this->empty = (bool)$empty;
        return $this;
    }

    public function getCanEmpty()
    {
        return (bool)$this->empty;
    }

    public function validate($value)
    {
        return ($this->getCanEmpty() && $value == '') || \Is::telephone($value);
    }

    public function getErrorMessage()
    {
        return "Le champ reçu n'est pas un téléphone valide";
    }
}
