<?php
namespace Tests\Cache\Driver;


/**
 * Class MemcachedTest
 * @package Tests\Cache\Driver
 */
class MemcachedTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $cache = new \FMUP\Cache\Driver\Memcached();
        $this->assertInstanceOf('\FMUP\Cache\CacheInterface', $cache, 'Instance of \FMUP\Cache\CacheInterface');
        $this->assertInstanceOf('\FMUP\Cache\Driver\Memcached', $cache, 'Instance of \FMUP\Cache\Driver\Memcached');
        $cache2 = new \FMUP\Cache\Driver\Memcached(array(\FMUP\Cache\Driver\Memcached::SETTINGS_CACHE_PREFIX => 'TestCase'));
        $this->assertNotSame($cache2, $cache, 'New cache instance must not be same');
        $this->assertNotEquals($cache2, $cache, 'New cache instance must not be equal');
        return $cache2;
    }

    /**
     * @depends testConstruct
     * @param \FMUP\Cache\Driver\Memcached $cache
     * @return \FMUP\Cache
     */
    public function testSetGet(\FMUP\Cache\Driver\Memcached $cache)
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
     * @param \FMUP\Cache\Driver\Memcached $cache
     */
    public function testHas(\FMUP\Cache\Driver\Memcached $cache)
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
     * @param \FMUP\Cache\Driver\Memcached $cache
     */
    public function testRemove(\FMUP\Cache\Driver\Memcached $cache)
    {
        if (!$cache->isAvailable()) {
            $this->markTestSkipped('Memcached is not available for testing');
        }

        $this->assertTrue($cache->has('test'), 'Test should exist');
        $return = $cache->remove('test');
        $this->assertSame($cache, $return, 'Set settings must return its instance');
        $this->assertFalse($cache->has('test'), 'Test should\'nt exist');
    }
}
