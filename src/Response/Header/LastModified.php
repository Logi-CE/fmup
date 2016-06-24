<?php
namespace FMUP\Response\Header;

use FMUP\Response\Header;

class LastModified extends Header
{
    const TYPE = 'Last-Modified';

    private $modifiedDate;

    /**
     * @param string|\DateTime|null $modifiedDate
     */
    public function __construct($modifiedDate = null)
    {
        $this->setModifiedDate($modifiedDate);
    }

    /**
     * Get the modified date
     * @return \DateTime
     */
    public function getModifiedDate()
    {
        if (!$this->modifiedDate) {
            $this->modifiedDate = new \DateTime();
        }
        return $this->modifiedDate;
    }

    /**
     * Define the modified date
     * @param string|\DateTime $modifiedDate
     * @throws \FMUP\Exception if date format is wrong
     * @return $this
     */
    public function setModifiedDate($modifiedDate = null)
    {
        if (is_string($modifiedDate) || is_null($modifiedDate)) {
            try {
                $modifiedDate = new \DateTime($modifiedDate);
            } catch (\Exception $e) {
                throw new \FMUP\Exception('Error on date format', $e->getCode(), $e);
            }
        }
        if (!$modifiedDate instanceof \DateTime) {
            throw new \FMUP\Exception('Error on date format');
        }
        $this->modifiedDate = $modifiedDate;
        return $this;
    }

    /**
     * Value returned in the header
     * @return string
     */
    public function getValue()
    {
        return $this->getModifiedDate()->format('D, d M Y H:i:s T');
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
