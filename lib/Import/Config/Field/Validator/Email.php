<?php
namespace FMUP\Import\Config\Field\Validator;

use FMUP\Import\Config\Field\Validator;

class Email implements Validator
{
    private $empty = false;

    /**
     * @param bool|false $empty
     */
    public function __construct($empty = false)
    {
        $this->setCanEmpty($empty);
    }

    /**
     * @param bool|false $empty
     * @return $this
     */
    public function setCanEmpty($empty = false)
    {
        $this->empty = (bool) $empty;
        return $this;
    }

    /**
     * @return bool
     */
    public function getCanEmpty()
    {
        return (bool)$this->empty;
    }

    public function validate($value)
    {
        $valid = false;
        if (($this->getCanEmpty() && $value == '') || \Is::courriel($value)) {
            $valid = true;
        }
        return $valid;
    }

    public function getErrorMessage()
    {
        return "Le champ reçu n'est pas un email valide";
    }
}
