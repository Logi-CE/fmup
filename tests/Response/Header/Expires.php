<?php
/**
 * Expires.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Response\Header;


class ExpiresTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $expires = new \FMUP\Response\Header\Expires();
        $this->assertInstanceOf('\FMUP\Response\Header', $expires);
        $this->assertInstanceOf('\DateTime', $expires->getExpireDate());
    }

    public function testSetGetExpireDate()
    {
        $expires = new \FMUP\Response\Header\Expires();
        $this->assertSame($expires, $expires->setExpireDate());
        $this->assertInstanceOf('\DateTime', $expires->getExpireDate());
        $this->assertEquals(new \DateTime(), $expires->getExpireDate());
        $dateTime = new \DateTime('+2 months');
        $this->assertSame($expires, $expires->setExpireDate($dateTime));
        $this->assertInstanceOf('\DateTime', $expires->getExpireDate());
        $this->assertSame($dateTime, $expires->getExpireDate());
    }

    public function testGetValue()
    {
        $expires = new \FMUP\Response\Header\Expires();
        $this->assertInstanceOf('\FMUP\Response\Header', $expires);
        $dateTime = new \DateTime('+2 months');
        $this->assertSame($expires, $expires->setExpireDate($dateTime));
        $this->assertInstanceOf('\DateTime', $expires->getExpireDate());
        $this->assertSame($dateTime->format('D, d M Y H:i:s T'), $expires->getValue());
    }

    public function testGetType()
    {
        $expires = new \FMUP\Response\Header\Expires();
        $this->assertSame(\FMUP\Response\Header\Expires::TYPE, $expires->getType());
    }
}
