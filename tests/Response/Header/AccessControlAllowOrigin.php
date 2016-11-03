<?php
/**
 * AccessControlAllowOrigin.php
 * @author: jyamin@castelis.com
 */

namespace Tests\Response\Header;


class AccessControlAllowOriginTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $accessControl = new \FMUP\Response\Header\AccessControlAllowOrigin('http://website.external');
        $this->assertInstanceOf('\FMUP\Response\Header', $accessControl);
        $this->assertSame('http://website.external', $accessControl->getOrigin());
        $this->assertSame('http://website.external', $accessControl->getValue());
    }

    public function testSetOrigin()
    {
        $accessControl = new \FMUP\Response\Header\AccessControlAllowOrigin('http://website.external');
        $this->assertSame($accessControl, $accessControl->setOrigin('http://unittest.url'));
        $this->assertSame('http://unittest.url', $accessControl->getOrigin());
        $this->assertSame('http://unittest.url', $accessControl->getValue());
    }

    public function testGetType()
    {
        $accessControl = new \FMUP\Response\Header\AccessControlAllowOrigin('http://website.external');
        $this->assertSame(\FMUP\Response\Header\AccessControlAllowOrigin::TYPE, $accessControl->getType());
    }
}
