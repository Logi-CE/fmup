<?php
namespace FMUP\Import\Config\Field\Validator;

use FMUP\Import\Config\Field\Validator;

/**
 * @author jyamin
 */
class RegExp implements Validator
{
    private $expression;

    /**
     * Construct enum validator
     * @param string $expression
     */
    public function __construct($expression = null)
    {
        $this->setExpression($expression);
    }

    /**
     * Set expression to Validate
     * @param string $expression
     * @return self
     */
    public function setExpression($expression)
    {
        $this->expression = $expression;
        return $this;
    }

    /**
     * Get expression to Validate
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    public function validate($value)
    {
        $valid = true;
        if (!preg_match($this->getExpression(), $value)) {
            $valid = false;
        }
        return $valid;
    }

    public function getErrorMessage()
    {
        return "Le champ reçu ne correspond pas au format autorisé";
    }
}
