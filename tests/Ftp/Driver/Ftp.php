<?php
namespace FMUPTests\Ftp\Driver;

use FMUP\Ftp;

class FtpTest extends \PHPUnit_Framework_TestCase
{
    public function testGetTimeout()
    {
        $method = new \ReflectionMethod(Ftp\Driver\Ftp::class, 'getTimeout');
        $method->setAccessible(true);

        $ftp = new Ftp\Driver\Ftp();
        $this->assertSame(90, $method->invoke($ftp));

        $ftp = new Ftp\Driver\Ftp(array(Ftp\Driver\Ftp::TIMEOUT => 50));
        $this->assertTrue(is_int($method->invoke($ftp)));
        $this->assertSame(50, $method->invoke($ftp));
    }

    public function testGetMode()
    {
        $method = new \ReflectionMethod(Ftp\Driver\Ftp::class, 'getMode');
        $method->setAccessible(true);

        $ftp = new Ftp\Driver\Ftp();
        $this->assertSame(FTP_ASCII, $method->invoke($ftp));

        $ftp = new Ftp\Driver\Ftp(array(Ftp\Driver\Ftp::MODE => FTP_BINARY));
        $this->assertTrue(is_int($method->invoke($ftp)));
        $this->assertSame(FTP_BINARY, $method->invoke($ftp));
    }

    public function testGetResumePos()
    {
        $method = new \ReflectionMethod(Ftp\Driver\Ftp::class, 'getResumePos');
        $method->setAccessible(true);

        $ftp = new Ftp\Driver\Ftp();
        $this->assertSame(0, $method->invoke($ftp));

        $ftp = new Ftp\Driver\Ftp(array(Ftp\Driver\Ftp::RESUME_POS => 50));
        $this->assertTrue(is_int($method->invoke($ftp)));
        $this->assertSame(50, $method->invoke($ftp));
    }

    public function testConnect()
    {
        $ftp = $this->getMockBuilder(Ftp\Driver\Ftp::class)->setMethods(array('ftpConnect'))->getMock();
        $ftp2 = $this->getMockBuilder(Ftp\Driver\Ftp::class)->setMethods(array('ftpConnect'))->getMock();
        $ftp3 = $this->getMockBuilder(Ftp\Driver\Ftp::class)
            ->setMethods(array('ftpConnect'))
            ->setConstructorArgs(array(array(Ftp\Driver\Ftp::TIMEOUT => 100)))
            ->getMock();
        $ftp->expects($this->once())
            ->method('ftpConnect')
            ->willReturn(fopen('php://stdin', 'r'))
            ->with($this->equalTo('host'), $this->equalTo(21), $this->equalTo(90));
        $ftp2->expects($this->once())
            ->method('ftpConnect')
            ->willReturn(fopen('php://stdin', 'r'))
            ->with($this->equalTo('host'), $this->equalTo(221), $this->equalTo(90));
        $ftp3->expects($this->once())
            ->method('ftpConnect')
            ->willReturn(fopen('php://stdin', 'r'))
            ->with($this->equalTo('host'), $this->equalTo(21), $this->equalTo(100));
        /**
         * @var $ftp Ftp\Driver\Ftp
         * @var $ftp2 Ftp\Driver\Ftp
         * @var $ftp3 Ftp\Driver\Ftp
         */
        $ret = $ftp->connect('host', 21);
        $ftp2->connect('host', 221);
        $ftp3->connect('host', 21);
        $this->assertTrue(is_resource($ftp->getSession()));
        $this->assertTrue(is_resource($ftp2->getSession()));
        $this->assertTrue(is_resource($ftp3->getSession()));
        $this->assertSame($ftp, $ret);
    }

    public function testLoginFail()
    {
        $ftp = $this->getMockBuilder(Ftp\Driver\Ftp::class)->setMethods(array('ftpLogin', 'getSession'))->getMock();
        $resource = fopen('php://stdin', 'r');
        $ftp->method('getSession')->willReturn($resource);
        $ftp->expects($this->once())->method('ftpLogin')->willReturn(false)->with(
            $this->equalTo($resource),
            $this->equalTo('login'),
            $this->equalTo('pass')
        );

        $this->expectException(Ftp\Exception::class);
        $this->expectExceptionMessage('Unable to login to the FTP server');
        /**
         * @var $ftp Ftp\Driver\Ftp
         */
        $ret = $ftp->login('login', 'pass');
        $this->assertFalse($ret);
    }

    public function testLoginSuccess()
    {
        $ftp = $this->getMockBuilder(Ftp\Driver\Ftp::class)->setMethods(array('ftpLogin', 'getSession'))->getMock();
        $resource = fopen('php://stdin', 'r');
        $ftp->method('getSession')->willReturn($resource);
        $ftp->expects($this->once())->method('ftpLogin')->willReturn(true)->with(
            $this->equalTo($resource),
            $this->equalTo('login'),
            $this->equalTo('pass')
        );

        /**
         * @var $ftp Ftp\Driver\Ftp
         */
        $ret = $ftp->login('login', 'pass');
        $this->assertTrue($ret);
    }

    public function testGetFile()
    {
        $ftp = $this->getMockBuilder(Ftp\Driver\Ftp::class)
            ->setMethods(array('ftpGet', 'getSession', 'getMode', 'getResumePos'))
            ->getMock();
        $resource = fopen('php://stdin', 'r');
        $ftp->method('getSession')->willReturn($resource);
        $ftp->method('getMode')->willReturn(FTP_ASCII);
        $ftp->method('getResumePos')->willReturn(0);

        $ftp->expects($this->once())->method('ftpGet')->willReturn(true)->with(
            $this->equalTo($resource),
            $this->equalTo('path/to/local/file.txt'),
            $this->equalTo('path/to/remote/file.txt'),
            $this->equalTo(FTP_ASCII),
            $this->equalTo(0)
        );
        /**
         * @var $ftp Ftp\Driver\Ftp
         */
        $ret = $ftp->get('path/to/local/file.txt', 'path/to/remote/file.txt');
        $this->assertTrue($ret);
    }

    public function testDeleteFile()
    {
        $ftp = $this->getMockBuilder(Ftp\Driver\Ftp::class)
            ->setMethods(array('ftpDelete', 'getSession'))
            ->getMock();
        $resource = fopen('php://stdin', 'r');
        $ftp->method('getSession')->willReturn($resource);
        $ftp->expects($this->exactly(2))
            ->method('ftpDelete')
            ->will($this->onConsecutiveCalls(true, false))
            ->with(
                $this->equalTo($resource),
                $this->equalTo('file')
            );

        /**
         * @var $ftp Ftp\Driver\Ftp
         */
        $ret1 = $ftp->delete('file');
        $ret2 = $ftp->delete('file');
        $this->assertTrue($ret1);
        $this->assertFalse($ret2);
    }

    public function testClose()
    {
        $ftp = $this->getMockBuilder(Ftp\Driver\Ftp::class)->setMethods(array('ftpClose', 'getSession'))->getMock();
        $resource = fopen('php://stdin', 'r');
        $ftp->method('getSession')->willReturn($resource);
        $ftp->expects($this->exactly(2))
            ->method('ftpClose')
            ->will($this->onConsecutiveCalls(true, false))
            ->with(
                $this->equalTo($resource)
            );
        /**
         * @var $ftp Ftp\Driver\Ftp
         */
        $this->assertTrue($ftp->close());
        $this->assertFalse($ftp->close());
    }
}
