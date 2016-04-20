<?php
namespace Tests\Cache\Driver;

/**
 * @todo check if this work
 * @todo must test settings (TTL)
 */
class ApcTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $cache = new \FMUP\Cache\Driver\Apc();
        $this->assertInstanceOf(\FMUP\Cache\CacheInterface::class, $cache, 'Instance of \FMUP\Cache\CacheInterface');
        $cache2 = new \FMUP\Cache\Driver\Apc(array(\FMUP\Cache\Driver\Apc::SETTING_CACHE_TYPE => \FMUP\Cache\Driver\Apc::CACHE_TYPE_USER));
        $this->assertNotSame($cache2, $cache, 'New cache instance must not be same');
        $this->assertNotEquals($cache2, $cache, 'New cache instance must not be equal');
        return $cache2;
    }

    /**
     * @depends testConstruct
     * @param \FMUP\Cache\Driver\Apc $cache
     * @return \FMUP\Cache
     */
    public function testSetGet(\FMUP\Cache\Driver\Apc $cache)
    {
        if (!$cache->isAvailable()) {
            $this->markTestSkipped('APC is not available for testing');
        }

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
            array('1', $this->getMockBuilder(\stdClass::class)->getMock()),
        );
        foreach ($test as $case) {
            try {
                $return = $cache->set($case[0], $case[1]);
            } catch (\FMUP\Cache\Exception $e) {
                $this->assertTrue(false, 'Unable to store ' . $case[1] . ' in ' . $case[0] . ' : ' . $e->getMessage());
            }
            $this->assertEquals($case[1], $cache->get($case[0]), 'Value is different on get');
            $this->assertSame($cache, $return, 'Set settings must return its instance');
        }
        return $cache;
    }

    /**
     * @depends testSetGet
     * @param \FMUP\Cache\Driver\Apc $cache
     */
    public function testHas(\FMUP\Cache\Driver\Apc $cache)
    {
        if (!$cache->isAvailable()) {
            $this->markTestSkipped('APC is not available for testing');
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
     * @param \FMUP\Cache\Driver\Apc $cache
     */
    public function testRemove(\FMUP\Cache\Driver\Apc $cache)
    {
        if (!$cache->isAvailable()) {
            $this->markTestSkipped('APC is not available for testing');
        }

        $this->assertTrue($cache->has('test'), 'Test should exist');
        $return = $cache->remove('test');
        $this->assertSame($cache, $return, 'Set settings must return its instance');
        $this->assertFalse($cache->has('test'), 'Test should\'nt exist');
    }
}
