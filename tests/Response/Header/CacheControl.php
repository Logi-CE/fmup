<?php
/**
 * CacheControl.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Response\Header;


class CacheControlTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $cacheControl = new \FMUP\Response\Header\CacheControl();
        $this->assertInstanceOf(\FMUP\Response\Header::class, $cacheControl);
        $this->assertInstanceOf(\DateTime::class, $cacheControl->getExpireDate());
        $this->assertEquals(time(), $cacheControl->getExpireDate()->getTimestamp());
        $this->assertSame(\FMUP\Response\Header\CacheControl::CACHE_TYPE_PUBLIC, $cacheControl->getCacheType());

        $cacheControl = new \FMUP\Response\Header\CacheControl(new \DateTime('-1 year'));
        $this->assertInstanceOf(\DateTime::class, $cacheControl->getExpireDate());
        $this->assertTrue((time() > $cacheControl->getExpireDate()->getTimestamp()));
        $this->assertSame(\FMUP\Response\Header\CacheControl::CACHE_TYPE_PUBLIC, $cacheControl->getCacheType());

        $cacheControl = new \FMUP\Response\Header\CacheControl(new \DateTime('-1 year'), \FMUP\Response\Header\CacheControl::CACHE_TYPE_PRIVATE);
        $this->assertInstanceOf(\DateTime::class, $cacheControl->getExpireDate());
        $this->assertTrue((time() > $cacheControl->getExpireDate()->getTimestamp()));
        $this->assertSame(\FMUP\Response\Header\CacheControl::CACHE_TYPE_PRIVATE, $cacheControl->getCacheType());
    }

    public function testSetGetCacheType()
    {
        $cacheControl = new \FMUP\Response\Header\CacheControl();
        $this->assertSame(\FMUP\Response\Header\CacheControl::CACHE_TYPE_PUBLIC, $cacheControl->getCacheType());
        $this->assertSame($cacheControl, $cacheControl->setCacheType(\FMUP\Response\Header\CacheControl::CACHE_TYPE_PRIVATE));
        $this->assertSame(\FMUP\Response\Header\CacheControl::CACHE_TYPE_PRIVATE, $cacheControl->getCacheType());
        $this->assertSame($cacheControl, $cacheControl->setCacheType('test'));
        $this->assertSame('test', $cacheControl->getCacheType());
    }

    public function testSetGetExpireDate()
    {
        $cacheControl = new \FMUP\Response\Header\CacheControl();
        $this->assertInstanceOf(\DateTime::class, $cacheControl->getExpireDate());
        $this->assertEquals(time(), $cacheControl->getExpireDate()->getTimestamp());
        $date = new \DateTime('-2 months');
        $this->assertSame($cacheControl, $cacheControl->setExpireDate($date));
        $this->assertSame($date, $cacheControl->getExpireDate());
    }

    public function testGetType()
    {
        $cacheControl = new \FMUP\Response\Header\CacheControl();
        $this->assertSame(\FMUP\Response\Header\CacheControl::TYPE, $cacheControl->getType());
    }

    public function testGetValue()
    {
        $cacheControl = new \FMUP\Response\Header\CacheControl(null, \FMUP\Response\Header\CacheControl::CACHE_TYPE_PRIVATE);
        $this->assertSame(\FMUP\Response\Header\CacheControl::CACHE_TYPE_PRIVATE . ', max-age=0, must-revalidate', $cacheControl->getValue());
        $cacheControl = new \FMUP\Response\Header\CacheControl(new \DateTime('+10 seconds'), 'testUnit');
        $this->assertSame('testUnit, max-age=10, must-revalidate', $cacheControl->getValue());
    }
}
