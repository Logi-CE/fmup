<?php
/**
 * Factory.php
 * @author: jmoulin@castelis.com
 */

namespace FMUP\Logger\Channel;

class Mock
{

}

namespace FMUPTests\Logger;

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
        $reflector = new \ReflectionClass(\FMUP\Logger\Factory::class);
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
        $this->assertInstanceOf(\FMUP\Logger\Factory::class, $factory);
        $factory2 = \FMUP\Logger\Factory::getInstance();
        $this->assertSame($factory, $factory2);
    }

    public function testGetChannel()
    {
        $factory = new FactoryMockLoggerFactory();
        $instance = $factory->getChannel(uniqid());
        $this->assertInstanceOf(\FMUP\Logger\Channel::class, $instance);
        $instance2 = $factory->getChannel(uniqid());
        $this->assertInstanceOf(\FMUP\Logger\Channel\Standard::class, $instance2);
        $this->assertNotSame($instance, $instance2);
        $this->assertInstanceOf(\FMUP\Logger\Channel\Error::class, $factory->getChannel('Error'));
    }

    public function testGetChannelFailsDueToInccorectChannel()
    {
        $factory = new FactoryMockLoggerFactory();
        $this->expectException(\FMUP\Logger\Exception::class);
        $this->expectExceptionMessage('Channel Mock is not correctly formatted');
        $factory->getChannel('Mock');
    }
}
