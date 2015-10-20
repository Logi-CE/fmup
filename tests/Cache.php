<?php
namespace Tests;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    private $driverMock;

    public function testGetInstance()
    {
        $reflector = new \ReflectionClass('\FMUP\Cache\Factory');
        $method = $reflector->getMethod('__construct');
        $this->assertTrue($method->isPrivate(), 'Construct must be private');
        $method = $reflector->getMethod('__clone');
        $this->assertTrue($method->isPublic(), 'Clone must be public');

        $cache = \FMUP\Cache::getInstance(\FMUP\Cache\Factory::DRIVER_RAM);
        $this->assertInstanceOf('\FMUP\Cache', $cache, 'Instance of \FMUP\Cache');
        $cache2 = \FMUP\Cache::getInstance(\FMUP\Cache\Factory::DRIVER_RAM);
        $this->assertSame($cache, $cache2, 'Must be same instance of the driver');
        $cache3 = \FMUP\Cache::getInstance('test');
        $this->assertNotSame($cache, $cache3, 'Cache test must not be same instance');
        return $cache;
    }

    /**
     * @depends testGetInstance
     * @param \FMUP\Cache $cache
     */
    public function testGetDriver(\FMUP\Cache $cache)
    {
        $this->assertEquals(
            \FMUP\Cache\Factory::DRIVER_RAM,
            $cache->getDriver(),
            'Driver must be set by default to the name of cache instance'
        );
    }

    /**
     * @depends testGetInstance
     * @param \FMUP\Cache $cache
     * @return \FMUP\Cache
     */
    public function testSetDriver(\FMUP\Cache $cache)
    {
        $return = $cache->setDriver('bob');
        $this->assertEquals('bob', $cache->getDriver(), 'Driver must be set to the name defined');
        $this->assertSame($cache, $return, 'Set Driver must return its instance');
        $return = $cache->setDriver('1');
        $this->assertEquals('1', $cache->getDriver(), 'Driver must be set to the name defined');
        $this->assertSame($cache, $return, 'Set Driver must return its instance');
        $return = $cache->setDriver(1);
        $this->assertEquals(1, $cache->getDriver(), 'Driver must be set to the name defined');
        $this->assertSame($cache, $return, 'Set Driver must return its instance');
        $cache->setDriver(\FMUP\Cache\Factory::DRIVER_RAM); //test suite must work with this driver
        return $cache;
    }

    /**
     * @depends testGetInstance
     * @param \FMUP\Cache $cache
     */
    public function testGetParams(\FMUP\Cache $cache)
    {
        $params = $cache->getParams();
        $this->assertEquals(array(), $params, 'Drivers settings must be empty on first load');
        $this->assertTrue(is_array($cache->getParams()), 'Drivers settings must be array');
    }

    /**
     * @depends testGetInstance
     * @param \FMUP\Cache $cache
     * @return \FMUP\Cache
     */
    public function testSetParams(\FMUP\Cache $cache)
    {
        $params = array('test' => 'test');
        $return = $cache->setParams($params);
        $this->assertEquals($params, $cache->getParams(), 'Drivers settings must same on set #1');
        $this->assertSame($cache, $return, 'Set settings must return its instance');
        $params = array('adkl' => array('jks' => 'eghjk'));
        $return = $cache->setParams($params);
        $this->assertEquals($params, $cache->getParams(), 'Drivers settings must same on set #2');
        $this->assertSame($cache, $return, 'Set settings must return its instance');
        $params = array();
        $return = $cache->setParams($params);
        $this->assertEquals($params, $cache->getParams(), 'Drivers settings must same on set #3');
        $this->assertSame($cache, $return, 'Set settings must return its instance');
        return $cache;
    }

    /**
     * @return \FMUP\Cache\Driver\Ram
     */
    private function getDriverMock()
    {
        if (!$this->driverMock) {
            $this->driverMock = new \FMUP\Cache\Driver\Ram();
        }
        return $this->driverMock;
    }

    /**
     * @depends testSetParams
     * @param \FMUP\Cache $cache
     * @return \FMUP\Cache
     */
    public function testSetCacheInstance(\FMUP\Cache $cache)
    {
        $return = $cache->setCacheInstance($this->getDriverMock());
        $this->assertEquals($cache, $return, 'Set Cache instance must return its instance');
        return $cache;
    }

    /**
     * @depends testSetCacheInstance
     * @param \FMUP\Cache $cache
     * @return \FMUP\Cache
     */
    public function testGetCacheInstance(\FMUP\Cache $cache)
    {
        $cache->setCacheInstance($this->getDriverMock());
        $this->assertSame($this->getDriverMock(), $cache->getCacheInstance(), 'Cache instance must return its cache instance');
        $this->assertInstanceOf('\FMUP\Cache\CacheInterface', $cache->getCacheInstance(), 'Cache instance must implements \FMUP\Cache\CacheInterface');
        return $cache;
    }

    public function testGetCacheInstanceFactory()
    {
        $cacheInstance = \FMUP\Cache::getInstance('TestFactory');
        $cacheInstance->setDriver(\FMUP\Cache\Factory::DRIVER_RAM);
        $this->assertInstanceOf('\FMUP\Cache\Driver\Ram', $cacheInstance->getCacheInstance(), 'Cache instance return a \FMUP\Cache\Driver\Ram Driver');
    }

    public function testGetCacheInstanceFactoryFail()
    {
        $cacheInstance = \FMUP\Cache::getInstance('TestFactoryFail');
        $cacheInstance->setDriver('DriverNotExists');
        try {
            $cacheInstance->getCacheInstance();
        } catch (\FMUP\Cache\Exception $e) {
            $this->assertTrue(true, 'Cache factory could not factory a non existing driver');
        }
    }

    /**
     * @depends testGetCacheInstance
     * @param \FMUP\Cache $cache
     * @return \FMUP\Cache
     */
    public function testSetGet(\FMUP\Cache $cache)
    {

        $test = array(
            array('test', 'test'),
            array('test', 'bob'),
            array('bob', 'bob'),
            array('bob', 'test'),
            array('bob', 1),
            array('bob', '1'),
            array('1', '1'),
            array('1', '2'),
            array('1', new \stdClass()),
            array('1', $this->getMockBuilder('\stdClass')->getMock()),
        );
        foreach ($test as $case) {
            $return = $cache->set($case[0], $case[1]);
            $this->assertSame($case[1], $cache->get($case[0]), 'Set settings must return its instance');
            $this->assertSame($cache, $return, 'Set settings must return its instance');
        }
        return $cache;
    }

    /**
     * @depends testSetGet
     * @param \FMUP\Cache $cache
     */
    public function testHas(\FMUP\Cache $cache)
    {
        $test = array(
            array('test', true),
            array('bob', true),
            array('1', true),
            array(1, true),
            array('notexists', false),
        );
        foreach ($test as $case) {
            $this->assertSame($case[1], $cache->has($case[0]), 'Test existence seems wrong');
            $this->assertTrue(is_bool($cache->has($case[0])), 'Return should be boolean');
        }
    }

    /**
     * @depends testSetGet
     * @param \FMUP\Cache $cache
     */
    public function testRemove(\FMUP\Cache $cache)
    {
        $this->assertTrue($cache->has('test'), 'Test should exist');
        $return = $cache->remove('test');
        $this->assertSame($cache, $return, 'Set settings must return its instance');
        $this->assertFalse($cache->has('test'), 'Test should\'nt exist');
    }
}
