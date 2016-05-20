<?php
namespace Tests;

use FMUP\Ftp;

class FtpInterfaceMockFtp implements Ftp\FtpInterface
{
    public function __construct($params = array())
    {
    }

    public function connect($host, $port = 21)
    {
    }

    public function login($user, $pass)
    {
    }

    public function get($localFile, $remoteFile)
    {
    }

    public function delete($file)
    {
    }

    public function close()
    {
    }
}

class FtpFactoryMockFtp extends Ftp\Factory
{
    public function __construct()
    {

    }
}

class FtpTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $ftp = $this->getMock(Ftp::class, null);
        $reflection = new \ReflectionProperty(Ftp::class, Ftp::DRIVER);
        $reflection->setAccessible(true);
        $this->assertSame(Ftp\Factory::DRIVER_FTP, $reflection->getValue($ftp));

        $ftp = $this->getMock(Ftp::class, null, array(array('driver' => 'unexisting driver')));
        $reflection = new \ReflectionProperty(Ftp::class, 'driver');
        $reflection->setAccessible(true);
        $this->assertSame('unexisting driver', $reflection->getValue($ftp));
    }

    public function testGetDriver()
    {
        $ftp = $this->getMock(Ftp::class, null);
        /**
         * @var $ftp Ftp
         * @var $factory Ftp\Factory
         */
        $retrievedDriver = $ftp->getDriver();
        $this->assertInstanceOf(Ftp\FtpInterface::class, $retrievedDriver);
        $this->assertSame($retrievedDriver, $ftp->getDriver());
    }
    
    public function testClose()
    {
        $ftpInterface = $this->getMock(FtpInterfaceMockFtp::class, array('close'));
        $ftp = $this->getMock(Ftp::class, array('getDriver'));

        $ftpInterface->expects($this->exactly(2))->method('close')->will($this->onConsecutiveCalls(true, false));
        $ftp->expects($this->exactly(2))->method('getDriver')->will(
            $this->onConsecutiveCalls($ftpInterface, $ftpInterface)
        );
        /**
         * @var $ftp Ftp
         */
        $this->assertTrue($ftp->close());
        $this->assertFalse($ftp->close());
    }

    public function testDelete()
    {
        $ftpInterface = $this->getMock(FtpInterfaceMockFtp::class, array('delete'));
        $ftp = $this->getMock(Ftp::class, array('getDriver'));

        $ftpInterface->expects($this->exactly(2))
            ->method('delete')
            ->will($this->onConsecutiveCalls(true, false))
            ->with($this->equalTo('path/to/remote/file.txt'));
        $ftp->expects($this->exactly(2))->method('getDriver')->will(
            $this->onConsecutiveCalls($ftpInterface, $ftpInterface)
        );
        /**
         * @var $ftp Ftp
         */
        $this->assertTrue($ftp->delete('path/to/remote/file.txt'));
        $this->assertFalse($ftp->delete('path/to/remote/file.txt'));
    }

    public function testGet()
    {
        $ftpInterface = $this->getMock(FtpInterfaceMockFtp::class, array('get'));
        $ftp = $this->getMock(Ftp::class, array('getDriver'));

        $ftpInterface->expects($this->exactly(2))
            ->method('get')
            ->will($this->onConsecutiveCalls(true, false))
            ->with($this->equalTo('path/to/local/file.txt'), $this->equalTo('path/to/remote/file.txt'));
        $ftp->expects($this->exactly(2))->method('getDriver')->will(
            $this->onConsecutiveCalls($ftpInterface, $ftpInterface)
        );
        /**
         * @var $ftp Ftp
         */
        $this->assertTrue($ftp->get('path/to/local/file.txt', 'path/to/remote/file.txt'));
        $this->assertFalse($ftp->get('path/to/local/file.txt', 'path/to/remote/file.txt'));
    }

    public function testLogin()
    {
        $ftpInterface = $this->getMock(FtpInterfaceMockFtp::class, array('login'));
        $ftp = $this->getMock(Ftp::class, array('getDriver'));

        $ftpInterface->expects($this->exactly(2))
            ->method('login')
            ->will($this->onConsecutiveCalls(true, false))
            ->with($this->equalTo('user'), $this->equalTo('pass'));
        $ftp->expects($this->exactly(2))->method('getDriver')->will(
            $this->onConsecutiveCalls($ftpInterface, $ftpInterface)
        );
        /**
         * @var $ftp Ftp
         */
        $this->assertTrue($ftp->login('user', 'pass'));
        $this->assertFalse($ftp->login('user', 'pass'));
    }

    public function testConnect()
    {
        $ftpInterface = $this->getMock(FtpInterfaceMockFtp::class, array('connect'));
        $ftpInterface2 = $this->getMock(FtpInterfaceMockFtp::class, array('connect'));
        $ftp = $this->getMock(Ftp::class, array('getDriver'));

        $ftpInterface->expects($this->once())
            ->method('connect')
            ->with($this->equalTo('host'), $this->equalTo('21'));
        $ftpInterface2->expects($this->once())
            ->method('connect')
            ->with($this->equalTo('host'), $this->equalTo('22'));

        $ftp->expects($this->exactly(2))->method('getDriver')->will(
            $this->onConsecutiveCalls($ftpInterface, $ftpInterface2)
        );
        /**
         * @var $ftp Ftp
         */
        $this->assertSame($ftp, $ftp->connect('host'));
        $this->assertSame($ftp, $ftp->connect('host', '22'));
    }
    
    public function testGetSetFactory()
    {
        $ftp = new Ftp;
        $factory = $ftp->getFactory();
        $this->assertInstanceOf(Ftp\Factory::class, $factory);
        $this->assertSame($factory, $ftp->getFactory());

        $factory = $this->getMock(FtpFactoryMockFtp::class);
        /** @var $factory Ftp\Factory */
        $reflection = new \ReflectionProperty(Ftp\Factory::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($ftp->getFactory(), $factory);
        $this->assertSame($ftp, $ftp->setFactory($factory));
        $this->assertSame($factory, $ftp->getFactory());
    }
}
