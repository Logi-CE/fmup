<?php
namespace FMUP\Cache\Driver;

class Mock
{

}

namespace Tests\Cache;

class Factory extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $factory = \FMUP\Cache\Factory::getInstance();
        $this->assertInstanceOf('\FMUP\Cache\Factory', $factory, 'Must be instance of \FMUP\Cache\Factory');
        $factory2 = \FMUP\Cache\Factory::getInstance();
        $this->assertSame($factory, $factory2, 'Must be only one instance of \FMUP\Cache\Factory');
        return $factory;
    }

    public function testCreate(\FMUP\Cache\Factory $factory)
    {
        try {
            $factory->create('unexist');
        } catch (\FMUP\Cache\Exception $e) {
            $this->assertTrue(true, 'Unable to create unexisting driver');
        }

        $driver = $factory->create('ram');
        $this->assertInstanceOf('\FMUP\Cache\Driver\Ram', $driver, 'Driver requested must be ram');

        $driver2 = $factory->create('ram', array('bob' => 'bob'));
        $this->assertInstanceOf('\FMUP\Cache\Driver\Ram', $driver2, 'Driver requested must be ram');
        $this->assertNotSame($driver, $driver2, 'Driver requested must not be same driver');

        $driver2 = $factory->create('ram', array('bob' => 'bob'));
        $this->assertInstanceOf('\FMUP\Cache\Driver\Ram', $driver2, 'Driver requested must be ram');
        $this->assertNotSame($driver, $driver2, 'Driver requested must not be same driver');

        $driver3 = $factory->create('apc', array('bob' => 'bob'));
        $this->assertInstanceOf('\FMUP\Cache\Driver\Apc', $driver3, 'Driver requested must be apc');
        $this->assertNotSame($driver2, $driver3, 'Driver requested must not be same driver');
        $this->assertEquals('bob', $driver3->getSetting('bob'), 'Driver settings must be set on construct');

        try {
            $driver4 = $factory->create('mock', array('bob' => 'bob'));
        } catch (\FMUP\Cache\Exception $e) {
            $this->assertEquals(
                'Unable to create \FMUP\Cache\Driver\Mock',
                $e->getMessage(),
                'Mock instance cannot be created due to its interface'
            );
            $this->assertTrue(class_exists('\FMUP\Cache\Driver\Mock'), 'Class exists \FMUP\Cache\Driver\Mock');
        }
    }
}
