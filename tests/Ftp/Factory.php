<?php
namespace Tests\Ftp;

use FMUP\Ftp;

class FactoryMockFtpFactory extends Ftp\Factory
{
    public function __construct()
    {

    }

    protected function getClassNameForDriver($driver)
    {
        return __NAMESPACE__ . '\\' . $driver;
    }
}

class DriverFailMock
{
}

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetInstance()
    {
        $reflector = new \ReflectionClass('\FMUP\Ftp\Factory');
        $method = $reflector->getMethod('__construct');
        $this->assertTrue($method->isPrivate(), 'Construct must be private');
        try {
            $reflector->getMethod('__clone')->invoke(Ftp\Factory::getInstance());
            $this->fail('Clone must fail');
        } catch (\ReflectionException $e) {
            $this->assertEquals(
                'Trying to invoke private method FMUP\Ftp\Factory::__clone() from scope ReflectionMethod',
                $e->getMessage()
            );
        }

        $factory = Ftp\Factory::getInstance();
        $this->assertInstanceOf('\FMUP\Ftp\Factory', $factory);
        $factory2 = Ftp\Factory::getInstance();
        $this->assertSame($factory, $factory2);
    }

    public function testCreateFailWhenClassDoesntExists()
    {
        $factory = new FactoryMockFtpFactory();
        $this->setExpectedException('\FMUP\Ftp\Exception');
        $factory->create('NotExistingDriver');
    }

    public function testCreateFailWhenClassNotCorrect()
    {
        $factory = new FactoryMockFtpFactory();
        $this->setExpectedException('\FMUP\Ftp\Exception');
        $factory->create('DriverFailMock');
    }

    public function testCreate()
    {
        $factory = Ftp\Factory::getInstance();
        $driverDefault = $factory->create();
        $this->assertInstanceOf('\FMUP\Ftp\FtpInterface', $driverDefault);
        $this->assertInstanceOf('\FMUP\Ftp\Driver\Ftp', $driverDefault);
        $driverDefault2 = $factory->create();
        $this->assertEquals($driverDefault, $driverDefault2);
        $this->assertNotSame($driverDefault, $driverDefault2);
        $driverDefault3 = $factory->create(FactoryMockFtpFactory::DRIVER_SFTP, array('test' => 1));
        $this->assertNotEquals($driverDefault, $driverDefault3);
        $this->assertInstanceOf('\FMUP\Ftp\Driver\Sftp', $driverDefault3);
    }
}
