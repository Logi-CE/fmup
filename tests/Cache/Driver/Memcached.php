<?php
namespace Tests\Cache\Driver;

use FMUP\Cache\Driver;

/**
 * Class MemcachedTest
 * @package Tests\Cache\Driver
 */
class MemcachedTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $cache = new Driver\Memcached();
        $this->assertInstanceOf('\FMUP\Cache\CacheInterface', $cache, 'Instance of ' . '\FMUP\Cache\CacheInterface');
        $this->assertInstanceOf('\FMUP\Cache\Driver\Memcached', $cache, 'Instance of ' . '\FMUP\Cache\Driver\Memcached');
        $cache2 = new Driver\Memcached(array(Driver\Memcached::SETTINGS_CACHE_PREFIX => 'TestCase'));
        $this->assertNotSame($cache2, $cache, 'New cache instance must not be same');
        $this->assertNotSame(clone $cache, $cache);
        $this->assertNotEquals($cache2, $cache, 'New cache instance must not be equal');

        /** @var $memcachedMock \Memcached */
        $memcachedMock = $this->getMockBuilder('\Memcached')
            ->setMethods(array('set', 'get', 'getResultMessage', 'getResultCode'))
            ->getMock();
        $cache3 = new Driver\Memcached(array(Driver\Memcached::SETTINGS_MEMCACHED => $memcachedMock));
        $this->assertSame($memcachedMock, $cache3->getMemcachedInstance());
        $cache3->setMemcachedInstance($memcachedMock);
        $this->assertSame($memcachedMock, $cache3->getMemcachedInstance());
    }

    public function testSetGetSettings()
    {
        $memcached = $this->getMockBuilder('\FMUP\Cache\Driver\Memcached')->setMethods(array('isAvailable'))->getMock();
        $memcached->method('isAvailable')->willReturn(true);
        /** @var $memcached Driver\Memcached */
        $testValue = 'testValue';
        $testKey = 'testKey';
        $this->assertSame($memcached, $memcached->setSetting($testKey, $testValue));
        $this->assertSame($testValue, $memcached->getSetting($testKey));
        $this->assertNull($memcached->getSetting('nonExistingKey'));
    }

    public function testRemove()
    {
        $cache = $this->getMockBuilder('\FMUP\Cache\Driver\Memcached')->setMethods(array('isAvailable'))->getMock();
        $cache->method('isAvailable')->willReturn(true);

        $memcached = $this->getMockBuilder('\Memcached')->setMethods(array('delete'))->getMock();
        $memcached->method('delete')->willReturn(true);
        /**
         * @var $memcached \Memcached
         * @var $cache Driver\Memcached
         */
        $cache->setMemcachedInstance($memcached);
        $this->assertSame($cache, $cache->remove('test'));
    }

    public function testRemoveWhenMemcachedFails()
    {
        $cache = $this->getMockBuilder('\FMUP\Cache\Driver\Memcached')->setMethods(array('isAvailable'))->getMock();
        $cache->method('isAvailable')->willReturn(true);

        $memcached = $this->getMockBuilder('\Memcached')->setMethods(array('delete'))->getMock();
        $memcached->method('delete')->willReturn(false);
        /**
         * @var $memcached \Memcached
         * @var $cache Driver\Memcached
         */
        $cache->setMemcachedInstance($memcached);
        $this->setExpectedException('\FMUP\Cache\Exception', 'Error while deleting key in memcached');
        $cache->remove('test');
    }

    public function testGetMemcachedInstanceFailsWhenNotAvailable()
    {
        $cache = $this->getMockBuilder('\FMUP\Cache\Driver\Memcached')->setMethods(array('isAvailable'))->getMock();
        $cache->method('isAvailable')->willReturn(false);
        $this->setExpectedException('\FMUP\Cache\Exception', 'Memcached is not available');
        /** @var $cache Driver\Memcached */
        $cache->getMemcachedInstance();
    }

    public function testGetMemcachedInstance()
    {
        $cache = $this->getMockBuilder('\FMUP\Cache\Driver\Memcached')
            ->setMethods(array('isAvailable', 'createMemcached'))
            ->getMock();
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('createMemcached')->willReturn($this->getMockBuilder('\Memcached')->getMock());
        /** @var $cache Driver\Memcached */
        $this->assertInstanceOf('\Memcached', $cache->getMemcachedInstance());
    }

    public function testHasWhenMemcachedNotAvailable()
    {
        $cache = $this->getMockBuilder('\FMUP\Cache\Driver\Memcached')->setMethods(array('isAvailable'))->getMock();
        $cache->method('isAvailable')->willReturn(false);
        $this->setExpectedException('\FMUP\Cache\Exception', 'Memcached is not available');
        /** @var $cache Driver\Memcached */
        $cache->has('bob');
    }

    public function testGetWhenMemcachedNotAvailable()
    {
        $cache = $this->getMockBuilder('\FMUP\Cache\Driver\Memcached')->setMethods(array('isAvailable'))->getMock();
        $cache->method('isAvailable')->willReturn(false);
        $this->setExpectedException('\FMUP\Cache\Exception', 'Memcached is not available');
        /** @var $cache Driver\Memcached */
        $cache->get('bob');
    }

    public function testSetWhenMemcachedNotAvailable()
    {
        $cache = $this->getMockBuilder('\FMUP\Cache\Driver\Memcached')->setMethods(array('isAvailable'))->getMock();
        $cache->method('isAvailable')->willReturn(false);
        $this->setExpectedException('\FMUP\Cache\Exception', 'Memcached is not available');
        /** @var $cache Driver\Memcached */
        $cache->set('bob', 'bob');
    }

    public function testRemoveWhenMemcachedNotAvailable()
    {
        $cache = $this->getMockBuilder('\FMUP\Cache\Driver\Memcached')->setMethods(array('isAvailable'))->getMock();
        $cache->method('isAvailable')->willReturn(false);
        $this->setExpectedException('\FMUP\Cache\Exception', 'Memcached is not available');
        /** @var $cache Driver\Memcached */
        $cache->remove('bob');
    }

    public function testIsAvailable()
    {
        $cache = new Driver\Memcached();
        $this->assertTrue(is_bool($cache->isAvailable()));
    }

    public function testHas()
    {
        $memcached = $this->getMockBuilder('\Memcached')->setMethods(array('getAllKeys'))->getMock();
        $memcached->method('getAllKeys')->willReturn(array('test', 'two'));

        $cache = $this->getMockBuilder('\FMUP\Cache\Driver\Memcached')
            ->setMethods(array('isAvailable', 'getMemcachedInstance'))
            ->getMock();
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('getMemcachedInstance')->willReturn($memcached);
        /** @var $cache Driver\Memcached */
        $this->assertTrue($cache->has('test'));
        $this->assertTrue($cache->has('two'));
        $this->assertFalse($cache->has('notexist'));
    }

    public function testGet()
    {
        $memcached = $this->getMockBuilder('\Memcached')->setMethods(array('get'))->getMock();
        $memcached->method('get')->with($this->equalTo('test'))->willReturn('ok');

        $cache = $this->getMockBuilder('\FMUP\Cache\Driver\Memcached')
            ->setMethods(array('isAvailable', 'getMemcachedInstance'))
            ->getMock();
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('getMemcachedInstance')->willReturn($memcached);
        /** @var $cache Driver\Memcached */
        $this->assertSame('ok', $cache->get('test'));
    }

    public function testSet()
    {
        $memcached = $this->getMockBuilder('\Memcached')->setMethods(array('set'))->getMock();
        $memcached->method('set')
            ->with($this->equalTo('testKey'), $this->equalTo('testValue'), $this->equalTo(20))
            ->willReturn(true);

        $cache = $this->getMockBuilder('\FMUP\Cache\Driver\Memcached')
            ->setMethods(array('isAvailable', 'getMemcachedInstance', 'getCacheKey', 'getSetting'))
            ->getMock();
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('getSetting')->with($this->equalTo(Driver\Memcached::SETTINGS_TTL_IN_SECOND))->willReturn(20);
        $cache->method('getMemcachedInstance')->willReturn($memcached);
        $cache->method('getCacheKey')->with($this->equalTo('testKey'))->willReturn('testKey');
        /** @var $cache Driver\Memcached */
        $this->assertSame($cache, $cache->set('testKey', 'testValue'));
    }

    public function testSetFailsWhenCannotSet()
    {
        $memcached = $this->getMockBuilder('\Memcached')
            ->setMethods(array('set', 'getResultMessage', 'getResultCode'))
            ->getMock();
        $memcached->method('set')
            ->with($this->equalTo('testKey'), $this->equalTo('testValue'), $this->equalTo(20))
            ->willReturn(false);
        $memcached->method('getResultCode')->willReturn(50);
        $memcached->method('getResultMessage')->willReturn('Failed');

        $cache = $this->getMockBuilder('\FMUP\Cache\Driver\Memcached')
            ->setMethods(array('isAvailable', 'getMemcachedInstance', 'getCacheKey', 'getSetting'))
            ->getMock();
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('getSetting')->with($this->equalTo(Driver\Memcached::SETTINGS_TTL_IN_SECOND))->willReturn(20);
        $cache->method('getMemcachedInstance')->willReturn($memcached);
        $cache->method('getCacheKey')->with($this->equalTo('testKey'))->willReturn('testKey');

        $this->setExpectedException('\FMUP\Cache\Exception', 'Error while inserting value in memcached : Failed', 50);
        /** @var $cache Driver\Memcached */
        $cache->set('testKey', 'testValue');
    }
}
