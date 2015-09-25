<?php
namespace FMUP\Import\Config\Field\Validator;

use FMUP\Import\Config\Field\Validator;

/**
 * @author jyamin
 */
class Enum implements Validator
{
    private $values;

    /**
     * Construct enum validator
     * @param array $values
     */
    public function __construct($values = null)
    {
        $this->setValues($values);
    }

    /**
     * Set values to Validate
     * @param array $values
     * @return self
     */
    public function setValues($values)
    {
        $this->values = $values;
        return $this;
    }

    /**
     * Get values to validate
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    public function validate($value)
    {
        $valid = true;
        if (!in_array($value, $this->getValues())) {
            $valid = false;
        }
        return $valid;
    }

    public function getErrorMessage()
    {
        return "Le champ reçu n'est pas valide";
    }
}
