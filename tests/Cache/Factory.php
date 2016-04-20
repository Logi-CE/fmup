<?php
namespace FMUP\Cache\Driver;

class Mock {

}

namespace Tests\Cache;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $reflector = new \ReflectionClass(\FMUP\Cache\Factory::class);
        $method = $reflector->getConstructor();
        $this->assertTrue($method->isPrivate(), 'Construct must be private');

        $method = $reflector->getMethod('__clone');
        $this->assertTrue($method->isPrivate(), 'Clone must be private');

        $factory = \FMUP\Cache\Factory::getInstance();
        $this->assertInstanceOf(\FMUP\Cache\Factory::class, $factory, 'Must be instance of ' . \FMUP\Cache\Factory::class);
        $factory2 = \FMUP\Cache\Factory::getInstance();
        $this->assertSame($factory, $factory2, 'Must be only one instance of ' . \FMUP\Cache\Factory::class);
        return $factory;
    }

    /**
     * @depends testConstruct
     * @param \FMUP\Cache\Factory $factory
     * @throws \FMUP\Cache\Exception
     */
    public function testCreate(\FMUP\Cache\Factory $factory)
    {
        try {
            $factory->create('unexist');
        } catch (\FMUP\Cache\Exception $e) {
            $this->assertTrue(true, 'Unable to create unexisting driver');
        }

        $driver = $factory->create('ram');
        $this->assertInstanceOf(\FMUP\Cache\Driver\Ram::class, $driver, 'Driver requested must be ram');

        $driver2 = $factory->create('ram', array('bob' => 'bob'));
        $this->assertInstanceOf(\FMUP\Cache\Driver\Ram::class, $driver2, 'Driver requested must be ram');
        $this->assertNotSame($driver, $driver2, 'Driver requested must not be same driver');

        $driver2 = $factory->create('ram', array('bob' => 'bob'));
        $this->assertInstanceOf(\FMUP\Cache\Driver\Ram::class, $driver2, 'Driver requested must be ram');
        $this->assertNotSame($driver, $driver2, 'Driver requested must not be same driver');

        $driver3 = $factory->create('apc', array('bob' => 'bob'));
        $this->assertInstanceOf(\FMUP\Cache\Driver\Apc::class, $driver3, 'Driver requested must be apc');
        $this->assertNotSame($driver2, $driver3, 'Driver requested must not be same driver');
        $this->assertEquals('bob', $driver3->getSetting('bob'), 'Driver settings must be set on construct');

        try {
            $driver4 = $factory->create('mock', array('bob' => 'bob'));
        } catch (\FMUP\Cache\Exception $e) {
            $this->assertEquals(
                'Unable to create ' . \FMUP\Cache\Driver\Mock::class,
                $e->getMessage(),
                'Mock instance cannot be created due to its interface'
            );
            $this->assertTrue(class_exists(\FMUP\Cache\Driver\Mock::class), 'Class exists ' . \FMUP\Cache\Driver\Mock::class);
        }
    }
}
