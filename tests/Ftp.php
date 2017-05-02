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

    public function put($remoteFile, $localFile)
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
        $ftp = $this->getMockBuilder('\FMUP\Ftp')->setMethods(null)->getMock();
        $reflection = new \ReflectionProperty('\FMUP\Ftp', Ftp::DRIVER);
        $reflection->setAccessible(true);
        $this->assertSame(Ftp\Factory::DRIVER_FTP, $reflection->getValue($ftp));

        $ftp = $this->getMockBuilder('\FMUP\Ftp')
            ->setMethods(null)
            ->setConstructorArgs(array(array('driver' => 'unexisting driver')))
            ->getMock();
        $reflection = new \ReflectionProperty('\FMUP\Ftp', 'driver');
        $reflection->setAccessible(true);
        $this->assertSame('unexisting driver', $reflection->getValue($ftp));
    }

    public function testGetDriver()
    {
        $ftp = $this->getMockBuilder('\FMUP\Ftp')->setMethods(null)->getMock();
        /**
         * @var $ftp Ftp
         * @var $factory Ftp\Factory
         */
        $retrievedDriver = $ftp->getDriver();
        $this->assertInstanceOf('\FMUP\Ftp\FtpInterface', $retrievedDriver);
        $this->assertSame($retrievedDriver, $ftp->getDriver());
    }
    
    public function testClose()
    {
        $ftpInterface = $this->getMockBuilder('\Tests\FtpInterfaceMockFtp')->setMethods(array('close'))->getMock();
        $ftp = $this->getMockBuilder('\FMUP\Ftp')->setMethods(array('getDriver'))->getMock();

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
        $ftpInterface = $this->getMockBuilder('\Tests\FtpInterfaceMockFtp')->setMethods(array('delete'))->getMock();
        $ftp = $this->getMockBuilder('\FMUP\Ftp')->setMethods(array('getDriver'))->getMock();

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
        $ftpInterface = $this->getMockBuilder('\Tests\FtpInterfaceMockFtp')->setMethods(array('get'))->getMock();
        $ftp = $this->getMockBuilder('\FMUP\Ftp')->setMethods(array('getDriver'))->getMock();

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

    public function testPut()
    {
        $ftpInterface = $this->getMockBuilder('\Tests\FtpInterfaceMockFtp')->setMethods(array('put'))->getMock();
        $ftp = $this->getMockBuilder('\FMUP\Ftp')->setMethods(array('getDriver'))->getMock();

        $ftpInterface->expects($this->exactly(2))
            ->method('put')
            ->will($this->onConsecutiveCalls(true, false))
            ->with($this->equalTo('path/to/remote/file.txt'), $this->equalTo('path/to/local/file.txt'));
        $ftp->expects($this->exactly(2))->method('getDriver')->will(
            $this->onConsecutiveCalls($ftpInterface, $ftpInterface)
        );
        /**
         * @var $ftp Ftp
         */
        $this->assertTrue($ftp->put('path/to/remote/file.txt', 'path/to/local/file.txt'));
        $this->assertFalse($ftp->put('path/to/remote/file.txt', 'path/to/local/file.txt'));
    }

    public function testLogin()
    {
        $ftpInterface = $this->getMockBuilder('\Tests\FtpInterfaceMockFtp')->setMethods(array('login'))->getMock();
        $ftp = $this->getMockBuilder('\FMUP\Ftp')->setMethods(array('getDriver'))->getMock();

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
        $ftpInterface = $this->getMockBuilder('\Tests\FtpInterfaceMockFtp')->setMethods(array('connect'))->getMock();
        $ftpInterface2 = $this->getMockBuilder('\Tests\FtpInterfaceMockFtp')->setMethods(array('connect'))->getMock();
        $ftp = $this->getMockBuilder('\FMUP\Ftp')->setMethods(array('getDriver'))->getMock();

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
        $this->assertInstanceOf('\FMUP\Ftp\Factory', $factory);
        $this->assertSame($factory, $ftp->getFactory());

        $factory = $this->getMockBuilder('\Tests\FtpFactoryMockFtp')->disableOriginalConstructor()->getMock();
        /** @var $factory Ftp\Factory */
        $reflection = new \ReflectionProperty('\FMUP\Ftp\Factory', 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($ftp->getFactory(), $factory);
        $this->assertSame($ftp, $ftp->setFactory($factory));
        $this->assertSame($factory, $ftp->getFactory());
    }
}
