<?php
namespace FMUP\Response\Header;

use FMUP\Response\Header;

class Location extends Header
{
    const TYPE = 'Location';

    /**
     * Redirect to a given path/URI
     * @param string $path
     */
    public function __construct($path)
    {
        $this->setValue($path);
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
