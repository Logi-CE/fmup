<?php
namespace FMUP\Import\Config\Field\Validator;

use FMUP\Import\Config\Field\Validator;

/**
 * @author jyamin
 */
class RegExp implements Validator
{
    private $expression;
    private $allowEmpty;

    /**
     * Construct enum validator
     * @param string $expression
     * @param bool $allowEmpty optional default false
     */
    public function __construct($expression = null, $allowEmpty = false)
    {
        $this->setExpression($expression);
        $this->setAllowEmpty($allowEmpty);
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

    public function getAllowEmpty()
    {
        return (bool)$this->allowEmpty;
    }

    public function setAllowEmpty($allowEmpty = false)
    {
        $this->allowEmpty = $allowEmpty;
        return $this;
    }

    public function validate($value)
    {
        return (bool)(preg_match($this->getExpression(), $value) || ($this->getAllowEmpty() && $value == ""));
    }

    public function getErrorMessage()
    {
        return "Le champ reçu ne correspond pas au format autorisé";
    }
}
