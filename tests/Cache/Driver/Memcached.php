<?php
namespace Tests\Cache\Driver;

use FMUP\Cache\Driver;

/**
 * Class MemcachedTest
 * @package Tests\Cache\Driver
 */
class MemcachedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \FMUP\Cache\Driver\Memcached
     */
    public function testConstruct()
    {
        $cache = new Driver\Memcached();
        $this->assertInstanceOf(\FMUP\Cache\CacheInterface::class, $cache, 'Instance of ' . \FMUP\Cache\CacheInterface::class);
        $this->assertInstanceOf(Driver\Memcached::class, $cache, 'Instance of ' . Driver\Memcached::class);
        $cache2 = new Driver\Memcached(array(Driver\Memcached::SETTINGS_CACHE_PREFIX => 'TestCase'));
        $this->assertNotSame($cache2, $cache, 'New cache instance must not be same');
        $this->assertNotSame(clone $cache, $cache);
        $this->assertNotEquals($cache2, $cache, 'New cache instance must not be equal');

        $memcachedMock = $this->getMock(\Memcached::class);
        $cache3 = new Driver\Memcached(array(Driver\Memcached::SETTINGS_MEMCACHED => $memcachedMock));
        $this->assertSame($memcachedMock, $cache3->getMemcachedInstance());
        $cache3->setMemcachedInstance($memcachedMock);
        $this->assertSame($memcachedMock, $cache3->getMemcachedInstance());
        return $cache2;
    }

    /**
     * @depends testConstruct
     * @param Driver\Memcached $cache
     * @return \FMUP\Cache
     */
    public function testSetGet(Driver\Memcached $cache)
    {
        if (!$cache->isAvailable()) {
            $this->markTestSkipped('Memcached is not available for testing');
        }

        try {
            $return = $cache->set('testError', 'testError');
            $this->assertTrue(false, 'You might not be able to define value in memcached without setting server');
        } catch (\FMUP\Cache\Exception $e) {
            $this->assertEquals(20, $e->getCode(), 'Unable to store testError because no server is defined // code');
            $this->assertTrue(true, 'Unable to store testError because no server is defined');
        }
        $cache->getMemcachedInstance()->addServer('127.0.0.1', 11211);

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
            try {
                $return = $cache->set($case[0], $case[1]);
            } catch (\FMUP\Cache\Exception $e) {
                $this->assertTrue(false, 'Unable to store ' . $case[1] . ' in ' . $case[0] . ' : ' . $e->getMessage());
                $return = false;
            }
            $this->assertEquals($case[1], $cache->get($case[0]), 'Value is different on get');
            $this->assertSame($cache, $return, 'Set settings must return its instance');
        }
        return $cache;
    }

    /**
     * @depends testSetGet
     * @param Driver\Memcached $cache
     */
    public function testHas(Driver\Memcached $cache)
    {
        if (!$cache->isAvailable()) {
            $this->markTestSkipped('Memcached is not available for testing');
        }

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
     * @param Driver\Memcached $cacheOriginal
     */
    public function testRemove(Driver\Memcached $cacheOriginal)
    {
        $cache = clone $cacheOriginal;
        if (!$cache->isAvailable()) {
            $this->markTestSkipped('Memcached is not available for testing');
        }

        $this->assertTrue($cache->has('test'), 'Test should exist');
        $return = $cache->remove('test');
        $this->assertSame($cache, $return, 'Set settings must return its instance');
        $this->assertFalse($cache->has('test'), 'Test should\'nt exist');
    }

    /**
     * @depends testConstruct
     * @param Driver\Memcached $memcachedOriginal
     */
    public function testRemoveWhenMemcachedFails(Driver\Memcached $memcachedOriginal)
    {
        $cache = clone $memcachedOriginal;

        $memcached = $this->getMock(\Memcached::class);
        $memcached->method('delete')->willReturn(false);
        $cache->setMemcachedInstance($memcached);
        $this->setExpectedException(\FMUP\Cache\Exception::class, 'Error while deleting key in memcached');
        $cache->remove('test');
    }

    public function testGetMemcachedInstance()
    {
        $cache = $this->getMock(Driver\Memcached::class, array('isAvailable'));
        $cache->method('isAvailable')->willReturn(false);
        $this->setExpectedException(\FMUP\Cache\Exception::class, 'Memcached is not available');
        /** @var $cache Driver\Memcached */
        $cache->getMemcachedInstance();
    }

    public function testHasWhenMemcachedNotAvailable()
    {
        $cache = $this->getMock(Driver\Memcached::class, array('isAvailable'));
        $cache->method('isAvailable')->willReturn(false);
        $this->setExpectedException(\FMUP\Cache\Exception::class, 'Memcached is not available');
        /** @var $cache Driver\Memcached */
        $cache->has('bob');
    }

    public function testGetWhenMemcachedNotAvailable()
    {
        $cache = $this->getMock(Driver\Memcached::class, array('isAvailable'));
        $cache->method('isAvailable')->willReturn(false);
        $this->setExpectedException(\FMUP\Cache\Exception::class, 'Memcached is not available');
        /** @var $cache Driver\Memcached */
        $cache->get('bob');
    }

    public function testSetWhenMemcachedNotAvailable()
    {
        $cache = $this->getMock(Driver\Memcached::class, array('isAvailable'));
        $cache->method('isAvailable')->willReturn(false);
        $this->setExpectedException(\FMUP\Cache\Exception::class, 'Memcached is not available');
        /** @var $cache Driver\Memcached */
        $cache->set('bob', 'bob');
    }

    public function testRemoveWhenMemcachedNotAvailable()
    {
        $cache = $this->getMock(Driver\Memcached::class, array('isAvailable'));
        $cache->method('isAvailable')->willReturn(false);
        $this->setExpectedException(\FMUP\Cache\Exception::class, 'Memcached is not available');
        /** @var $cache Driver\Memcached */
        $cache->remove('bob');
    }

    /**
     * @depends testConstruct
     * @param Driver\Memcached $memcached
     */
    public function testSetGetSettings(Driver\Memcached $memcached)
    {
        $testValue = 'testValue';
        $testKey = 'testKey';
        $this->assertSame($memcached, $memcached->setSetting($testKey, $testValue));
        $this->assertSame($testValue, $memcached->getSetting($testKey));
        $this->assertNull($memcached->getSetting('nonExistingKey'));
    }

}
