<?php
namespace FMUP\Response\Header;

use FMUP\Response\Header;

class Pragma extends Header
{
    const TYPE = 'Pragma';
    const MODE_CACHE = 'cache';
    const MODE_NOCACHE = 'no-cache';

    private $mode;

    /**
     * @param string $mode
     */
    public function __construct($mode)
    {
        $this->setMode($mode);
    }

    /**
     * Get the caching mode
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Define the caching mode
     * @param string $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * Value returned in the header
     * @return string
     */
    public function getValue()
    {
        return $this->getMode();
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