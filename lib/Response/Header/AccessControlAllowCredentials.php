<?php
namespace FMUP\Response\Header;

use FMUP\Response\Header;

class AccessControlAllowCredentials extends Header
{
    const TYPE = 'Access-Control-Allow-Credentials';

    const ALLOW_TRUE = 'true';
    const ALLOW_FALSE = 'false';

    private $allow = false;

    /**
     * @param bool $allow
     */
    public function __construct($allow)
    {
        $this->setAllow($allow);
    }

    /**
     * @return boolean
     */
    public function isAllow()
    {
        return $this->allow;
    }

    /**
     * @param boolean $allow
     */
    public function setAllow($allow)
    {
        $this->allow = $allow;
    }

    /**
     * Value returned in the header
     * @return string
     */
    public function getValue()
    {
        return $this->isAllow() ? self::ALLOW_TRUE : self::ALLOW_FALSE;
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
