<?php
/**
 * AccessControlAllowCredentials.php
 * @author: jyamin@castelis.com
 */
namespace Tests\Response\Header;

use FMUP\Response\Header\AccessControlAllowCredentials;

class AccessControlAllowCredentialsTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $accessControl = new AccessControlAllowCredentials(true);
        $this->assertInstanceOf('\FMUP\Response\Header', $accessControl);
        $this->assertTrue($accessControl->isAllow());
        $this->assertSame('true', $accessControl->getValue());

        $accessControl = new AccessControlAllowCredentials(false);
        $this->assertInstanceOf('\FMUP\Response\Header', $accessControl);
        $this->assertFalse($accessControl->isAllow());
        $this->assertSame('false', $accessControl->getValue());
    }

    public function testSetAllow()
    {
        $accessControl = new AccessControlAllowCredentials(true);
        $this->assertSame($accessControl, $accessControl->setAllow(false));
        $this->assertFalse($accessControl->isAllow());
        $this->assertSame('false', $accessControl->getValue());
    }

    public function testGetType()
    {
        $accessControl = new AccessControlAllowCredentials(true);
        $this->assertSame(AccessControlAllowCredentials::TYPE, $accessControl->getType());
    }
}
