<?php
namespace FMUP\Response\Header;

use FMUP\Response\Header;

class CacheControl extends Header
{
    const TYPE = 'Cache-Control';
    const CACHE_TYPE_PUBLIC = 'public';
    const CACHE_TYPE_PRIVATE = 'private';

    private $expireDate;
    private $cacheType;

    /**
     * @param \DateTime $dateTime
     * @param string $cacheType
     */
    public function __construct(\DateTime $dateTime, $cacheType = self::CACHE_TYPE_PUBLIC)
    {
        $this->setExpireDate($dateTime);
        $this->setCacheType($cacheType);
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
        return $this->getExpireDate()->getTimestamp() - $now->getTimestamp();
    }

    /**
     * Define the cacheType (public or private)
     * @param string $cacheType
     * @return $this
     */
    public function setCacheType($cacheType = self::CACHE_TYPE_PUBLIC)
    {
        $this->cacheType = $cacheType;
        return $this;
    }

    /**
     * Get cacheType
     * @return string
     */
    public function getCacheType()
    {
        return $this->cacheType;
    }

    /**
     * Value returned in the header
     * @return string
     */
    public function getValue()
    {
        return $this->getCacheType() . ', max-age=' . $this->getExpireDateInSec() . ', must-revalidate';
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
