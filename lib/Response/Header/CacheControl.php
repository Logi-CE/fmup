<?php
namespace FMUP\Response\Header;

use FMUP\Response\Header;

class CacheControl extends Header
{
    const TYPE = 'Cache-Control';

    private $expireDate;

    /**
     * @param \DateTime $dateTime
     */
    public function __construct(\DateTime $dateTime)
    {
        $this->setExpireDate($dateTime);
    }

    /**
     * @return \DateTime
     */
    public function getExpireDate()
    {
        return $this->expireDate;
    }

    /**
     * @param \DateTime $dateTime
     * @return $this
     */
    public function setExpireDate(\DateTime $dateTime)
    {
        $this->expireDate = $dateTime;
        return $this;
    }

    /**
     * @return int
     */
    private function getExpireDateInSec()
    {
        $now = new \DateTime();
        return $now->getTimestamp() - $this->getExpireDate()->getTimestamp();
    }

    /**
     * Value returned in the header
     * @return string
     */
    public function getValue()
    {
        return 'max-age=' . $this->getExpireDateInSec();
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
