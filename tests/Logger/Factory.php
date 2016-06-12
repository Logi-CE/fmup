<?php
/**
 * Factory.php
 * @author: jmoulin@castelis.com
 */

namespace FMUP\Logger\Channel;

class Mock
{

}

namespace Tests\Logger;

class FactoryMockLoggerFactory extends \FMUP\Logger\Factory
{
    public function __construct()
    {
    }
}

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetInstance()
    {
        $reflector = new \ReflectionClass('\FMUP\Logger\Factory');
        $method = $reflector->getMethod('__construct');
        $this->assertTrue($method->isPrivate(), 'Construct must be private');
        try {
            $reflector->getMethod('__clone')->invoke(\FMUP\Logger\Factory::getInstance());
            $this->fail('Clone must fail');
        } catch (\ReflectionException $e) {
            $this->assertEquals(
                'Trying to invoke private method FMUP\Logger\Factory::__clone() from scope ReflectionMethod',
                $e->getMessage()
            );
        }

        $factory = \FMUP\Logger\Factory::getInstance();
        $this->assertInstanceOf('\FMUP\Logger\Factory', $factory);
        $factory2 = \FMUP\Logger\Factory::getInstance();
        $this->assertSame($factory, $factory2);
    }

    public function testGetChannel()
    {
        $factory = new FactoryMockLoggerFactory();
        $instance = $factory->getChannel(uniqid());
        $this->assertInstanceOf('\FMUP\Logger\Channel', $instance);
        $instance2 = $factory->getChannel(uniqid());
        $this->assertInstanceOf('\FMUP\Logger\Channel\Standard', $instance2);
        $this->assertNotSame($instance, $instance2);
        $this->assertInstanceOf('\FMUP\Logger\Channel\Error', $factory->getChannel('Error'));
    }

    public function testGetChannelFailsDueToInccorectChannel()
    {
        $factory = new FactoryMockLoggerFactory();
        $this->setExpectedException('\FMUP\Logger\Exception', 'Channel Mock is not correctly formatted');
        $factory->getChannel('Mock');
    }
}
