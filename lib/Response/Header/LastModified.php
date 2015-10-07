<?php
namespace FMUP\Response\Header;

use FMUP\Response\Header;

class LastModified extends Header
{
    const TYPE = 'Last-Modified';

    private $modifiedDate;

    /**
     * @param string $modifiedDate
     */
    public function __construct($modifiedDate)
    {
        $this->setModifiedDate($modifiedDate);
    }

    /**
     * Get the modified date
     * @return string
     */
    public function getModifiedDate()
    {
        return $this->modifiedDate;
    }

    /**
     * Define the modified date
     * @param string $modifiedDate
     * @return $this
     */
    public function setModifiedDate($modifiedDate)
    {
        $this->modifiedDate = $modifiedDate;
        return $this;
    }

    /**
     * Value returned in the header
     * @return string
     */
    public function getValue()
    {
        return $this->getModifiedDate();
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
