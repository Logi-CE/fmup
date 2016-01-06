<?php
namespace FMUP\Import\Config\Field\Validator;

use FMUP\Import\Config\Field\Validator;

/**
 * @author jyamin
 */
class RegExp implements Validator
{
    private $expression;
    private $allow_empty;

    /**
     * Construct enum validator
     * @param string $expression
     */
    public function __construct($expression = null, $allow_empty = false)
    {
        $this->setExpression($expression);
        $this->setAllowEmpty($allow_empty);
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
    
    public function getAllowEmpty() {
        return $this->allow_empty;
    }
    
    public function setAllowEmpty($allow_empty) {
        $this->allow_empty = $allow_empty;
        return $this;
    }

    public function validate($value)
    {
        $res = false;
        if (
                preg_match($this->getExpression(), $value) 
                || ($this->getAllowEmpty() && $value == "")
        ) {
            $res = true;
        }
        return $res;
    }

    public function getErrorMessage()
    {
        return "Le champ reçu ne correspond pas au format autorisé";
    }
}
