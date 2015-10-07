<?php
namespace FMUP\Response;

abstract class Header
{
    /**
     * @var string
     */
    protected $value;

    /**
     * Define value to use
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Retrieve defined value
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Type for the header. Can be used to determine header to send
     * @return string
     */
    abstract public function getType();

    /**
     * Displays the header
     * @return $this
     */
    public function render()
    {
        header($this->getType() . ': ' . $this->getValue());
        return $this;
    }
}
