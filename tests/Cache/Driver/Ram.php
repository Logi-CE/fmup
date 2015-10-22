<?php
namespace Tests\Cache\Driver;

/**
 * Created by PhpStorm.
 * User: jmoulin
 * Date: 19/10/2015
 * Time: 10:17
 */
class RamTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $cache = new \FMUP\Cache\Driver\Ram();
        $this->assertInstanceOf('\FMUP\Cache\CacheInterface', $cache, 'Instance of \FMUP\Cache\CacheInterface');
        $cache2 = new \FMUP\Cache\Driver\Ram(array(''));
        $this->assertNotSame($cache2, $cache, 'New cache instance must not be same');
        $this->assertNotEquals($cache2, $cache, 'New cache instance must not be equal');
        return $cache;
    }

    /**
     * @depends testConstruct
     * @param \FMUP\Cache\Driver\Ram $cache
     * @return \FMUP\Cache
     */
    public function testSetGet(\FMUP\Cache\Driver\Ram $cache)
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
            $this->assertEquals($case[1], $cache->get($case[0]), 'Value is different on get');
            $this->assertSame($cache, $return, 'Set settings must return its instance');
        }
        return $cache;
    }

    /**
     * @depends testSetGet
     * @param \FMUP\Cache\Driver\Ram $cache
     */
    public function testHas(\FMUP\Cache\Driver\Ram $cache)
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
     * @param \FMUP\Cache\Driver\Ram $cache
     */
    public function testRemove(\FMUP\Cache\Driver\Ram $cache)
    {
        $this->assertTrue($cache->has('test'), 'Test should exist');
        $return = $cache->remove('test');
        $this->assertSame($cache, $return, 'Set settings must return its instance');
        $this->assertFalse($cache->has('test'), 'Test should\'nt exist');
    }
}
