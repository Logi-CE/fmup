<?php
namespace FMUP\Response\Header;

use FMUP\Response\Header;

class AccessControlAllowOrigin extends Header
{
    const TYPE = 'Access-Control-Allow-Origin';

    private $origin;

    /**
     * @param string $origin
     */
    public function __construct($origin)
    {
        $this->setOrigin($origin);
    }

    /**
     * @return string
     */
    public function getOrigin()
    {
        return (string)$this->origin;
    }

    /**
     * @param mixed $origin
     * @return $this
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;
        return $this;
    }

    /**
     * Value returned in the header
     * @return string
     */
    public function getValue()
    {
        return (string)$this->getOrigin();
    }

    /**
     * Type for the header. Can be used to determine header to send
     * @return string
     */
    public function getType()
    {
        return self::TYPE;
    }
}
