<?php
namespace FMUP\Response\Header;

use FMUP\Response\Header;

class Expires extends Header
{
    const TYPE = 'Expires';

    private $expireDate;

    /**
     * @param \DateTime $expireDate
     */
    public function __construct(\DateTime $expireDate)
    {
        $this->setExpireDate($expireDate);
    }

    /**
     * Get the expire date
     * @return \DateTime
     */
    public function getExpireDate()
    {
        return $this->expireDate;
    }

    /**
     * Define the expire date
     * @param \DateTime $expireDate
     */
    public function setExpireDate(\DateTime $expireDate)
    {
        $this->expireDate = $expireDate;
        return $this;
    }

    /**
     * Value returned in the header
     * @return string
     */
    public function getValue()
    {
        return $this->getExpireDate()->format('D, d M Y H:i:s T');
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
