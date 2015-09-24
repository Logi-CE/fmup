<?php
namespace FMUP\Response\Header;

use FMUP\Response\Header;

class ContentLength extends Header
{
    const TYPE = 'Content-Length';

    private $contentLength;

    /**
     * @param int $contentLength
     */
    public function __construct($contentLength)
    {
        $this->setContentLength($contentLength);
    }

    /**
     * Get the content length
     * @return int
     */
    public function getContentLength()
    {
        return $this->contentLength;
    }

    /**
     * Define the content length
     * @param int $contentLength
     */
    public function setContentLength($contentLength)
    {
        $this->contentLength = $contentLength;
        return $this;
    }

    /**
     * Value returned in the header
     * @return string
     */
    public function getValue()
    {
        return $this->getContentLength();
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
