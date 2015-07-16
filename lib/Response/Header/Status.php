<?php
namespace FMUP\Response\Header;

use FMUP\Response\Header;

/**
 * Class Status
 * @package FMUP\Response\Header
 * @todo must implement http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
 */
class Status extends Header
{
    const TYPE = 'Status';

    const VALUE_OK = '200 OK';
    const VALUE_FORBIDDEN = '403 Forbidden';
    const VALUE_NOT_FOUND = '404 Not Found';
    const VALUE_INTERNAL_SERVER_ERROR = '500 Internal Server Error';

    /**
     * @param string $value
     */
    public function __construct($value = self::VALUE_OK)
    {
        $this->setValue($value);
    }

    /**
     * @return $this
     */
    public function render()
    {
        header('HTTP/1.1 ' . $this->getValue());
        return $this;
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
