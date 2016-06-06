<?php
namespace Tests\Ftp;

use FMUP\Ftp;

class FtpAbstractTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSessionWithoutSession()
    {
        $ftpAbstract = $this->getMockBuilder(Ftp\FtpAbstract::class)
            ->getMockForAbstractClass();

        $this->expectException(Ftp\Exception::class);
        $this->expectExceptionMessage('Unable to connect to the FTP server');
        /**
         * @var $ftpAbstract Ftp\FtpAbstract
         */
        $ftpAbstract->getSession();
    }

    public function testConstructorAndGettingSettings()
    {
        $method = new \ReflectionMethod(Ftp\FtpAbstract::class, 'getSettings');
        $method->setAccessible(true);

        $ftpAbstract = $this->getMockBuilder(Ftp\FtpAbstract::class)
            ->setConstructorArgs(array(array('key1' => 'val1', 'key2' => 'val2')))
            ->getMock();

        $this->assertSame('val1', $method->invoke($ftpAbstract, 'key1'));
        $this->assertSame(array('key1' => 'val1', 'key2' => 'val2'), $method->invoke($ftpAbstract));
        $this->assertNull($method->invoke($ftpAbstract, 'toto'));
    }

    public function testGetSetSession()
    {
        $ftpAbstract = $this->getMockBuilder(Ftp\FtpAbstract::class)->getMockForAbstractClass();

        $resource = fopen('php://stdin', 'r');
        /**
         * @var $ftpAbstract Ftp\FtpAbstract
         */
        $ftpAbstract->setSession($resource);
        $this->assertSame($resource, $ftpAbstract->getSession());
    }
}
