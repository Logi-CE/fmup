<?php
namespace FMUP\Crypt\Driver;

class DriverMock
{
}

/**
 * Factory.php
 * @author: jmoulin@castelis.com
 */
namespace Tests\Crypt;

use FMUP\Crypt\Factory;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return Factory
     */
    public function testGetInstance()
    {
        $reflector = new \ReflectionClass('\FMUP\Crypt\Factory');
        $method = $reflector->getMethod('__construct');
        $this->assertTrue($method->isPrivate(), 'Construct must be private');
        try {
            $reflector->getMethod('__clone')->invoke(Factory::getInstance());
            $this->fail('Clone must fail');
        } catch (\ReflectionException $e) {
            $this->assertEquals(
                'Trying to invoke private method FMUP\Crypt\Factory::__clone() from scope ReflectionMethod',
                $e->getMessage()
            );
        }

        $factory = Factory::getInstance();
        $this->assertInstanceOf('\FMUP\Crypt\Factory', $factory);
        $factory2 = Factory::getInstance();
        $this->assertSame($factory, $factory2);
        return $factory;
    }

    /**
     * @depends testGetInstance
     */
    public function testCreate(Factory $factory)
    {
        $driver = $factory->create();
        $this->assertInstanceOf('\FMUP\Crypt\CryptInterface', $driver);
        $this->assertInstanceOf('\FMUP\Crypt\Driver\Md5', $driver);
        $driver2 = $factory->create();
        $this->assertInstanceOf('\FMUP\Crypt\CryptInterface', $driver);
        $this->assertInstanceOf('\FMUP\Crypt\Driver\Md5', $driver);
        $this->assertNotSame($driver, $driver2);
    }

    /**
     * @depends testGetInstance
     * @param Factory $factory
     */
    public function testCreateWhenDriverDoNotExists(Factory $factory)
    {
        $this->setExpectedException('\FMUP\Crypt\Exception', 'Unable to create FMUP\Crypt\Driver\Random');
        $factory->create('Random');
    }

    /**
     * @depends testGetInstance
     * @param Factory $factory
     */
    public function testCreateWhenDriverNotCorrect(Factory $factory)
    {
        $this->setExpectedException('\FMUP\Crypt\Exception', 'Unable to create FMUP\Crypt\Driver\DriverMock');
        $factory->create('DriverMock');
    }
}
