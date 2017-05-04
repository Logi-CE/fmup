<?php
namespace Tests\Cache\Driver;

use FMUP\Cache\Driver;

/**
 * Class RedisTest
 * @package Tests\Cache\Driver
 */
class RedisTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $cache = new Driver\Redis();
        $this->assertInstanceOf(\FMUP\Cache\CacheInterface::class, $cache, 'Instance of ' . \FMUP\Cache\CacheInterface::class);
        $this->assertInstanceOf(\FMUP\Cache\Driver\Redis::class, $cache, 'Instance of ' . \FMUP\Cache\Driver\Redis::class);
        $cache2 = new Driver\Redis(array(Driver\Redis::SETTINGS_CACHE_PREFIX => 'TestCase'));
        $this->assertNotSame($cache2, $cache, 'New cache instance must not be same');
        $this->assertNotSame(clone $cache, $cache);
        $this->assertNotEquals($cache2, $cache, 'New cache instance must not be equal');

        /** @var $redisMock \Predis\Client */
        $redisMock = $this->getMockBuilder(\Predis\Client::class)
            ->setMethods(array('set', 'get'))
            ->getMock();
        $cache3 = new Driver\Redis(array(Driver\Redis::SETTINGS_REDIS => $redisMock));
        $this->assertSame($redisMock, $cache3->getRedisInstance());
        $cache3->setRedisInstance($redisMock);
        $this->assertSame($redisMock, $cache3->getRedisInstance());
    }

    public function testSetGetSettings()
    {
        $redis = $this->getMockBuilder(\FMUP\Cache\Driver\Redis::class)->setMethods(array('isAvailable'))->getMock();
        $redis->method('isAvailable')->willReturn(true);
        /** @var $redis Driver\Redis */
        $testValue = 'testValue';
        $testKey = 'testKey';
        $this->assertSame($redis, $redis->setSetting($testKey, $testValue));
        $this->assertSame($testValue, $redis->getSetting($testKey));
        $this->assertNull($redis->getSetting('nonExistingKey'));
    }

    public function testRemove()
    {
        $cache = $this->getMockBuilder(\FMUP\Cache\Driver\Redis::class)->setMethods(array('isAvailable'))->getMock();
        $cache->method('isAvailable')->willReturn(true);

        $redis = $this->getMockBuilder(\Predis\Client::class)->setMethods(array('del'))->getMock();
        $redis->method('del')->willReturn(true);
        /**
         * @var $redis \Predis\Client
         * @var $cache Driver\Redis
         */
        $cache->setRedisInstance($redis);
        $this->assertSame($cache, $cache->remove('test'));
    }

    public function testRemoveWhenRedisFails()
    {
        $cache = $this->getMockBuilder(\FMUP\Cache\Driver\Redis::class)->setMethods(array('isAvailable'))->getMock();
        $cache->method('isAvailable')->willReturn(true);

        $redis = $this->getMockBuilder(\Predis\Client::class)->setMethods(array('del'))->getMock();
        $redis->method('del')->willReturn(false);
        /**
         * @var $redis \Predis\Client
         * @var $cache Driver\Redis
         */
        $cache->setRedisInstance($redis);
        $this->expectException(\FMUP\Cache\Exception::class);
        $this->expectExceptionMessage('Error while deleting key in redis');
        $cache->remove('test');
    }

    public function testGetRedisInstance()
    {
        $cache = $this->getMockBuilder(\FMUP\Cache\Driver\Redis::class)
            ->setMethods(array('isAvailable', 'createRedis'))
            ->getMock();
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('createRedis')->willReturn($this->getMockBuilder(\Predis\Client::class)->getMock());
        /** @var $cache Driver\Redis */
        $this->assertInstanceOf(\Predis\Client::class, $cache->getRedisInstance());
    }

    public function testHasWhenRedisNotAvailable()
    {
        $cache = $this->getMockBuilder(\FMUP\Cache\Driver\Redis::class)->setMethods(array('isAvailable'))->getMock();
        $cache->method('isAvailable')->willReturn(false);
        $this->expectException(\FMUP\Cache\Exception::class);
        $this->expectExceptionMessage('Redis is not available');
        /** @var $cache Driver\Redis */
        $cache->has('bob');
    }

    public function testGetWhenRedisNotAvailable()
    {
        $cache = $this->getMockBuilder(\FMUP\Cache\Driver\Redis::class)->setMethods(array('isAvailable'))->getMock();
        $cache->method('isAvailable')->willReturn(false);
        $this->expectException(\FMUP\Cache\Exception::class);
        $this->expectExceptionMessage('Redis is not available');
        /** @var $cache Driver\Redis */
        $cache->get('bob');
    }

    public function testSetWhenRedisNotAvailable()
    {
        $cache = $this->getMockBuilder(\FMUP\Cache\Driver\Redis::class)->setMethods(array('isAvailable'))->getMock();
        $cache->method('isAvailable')->willReturn(false);
        $this->expectException(\FMUP\Cache\Exception::class);
        $this->expectExceptionMessage('Redis is not available');
        /** @var $cache Driver\Redis */
        $cache->set('bob', 'bob');
    }

    public function testRemoveWhenRedisNotAvailable()
    {
        $cache = $this->getMockBuilder(\FMUP\Cache\Driver\Redis::class)->setMethods(array('isAvailable'))->getMock();
        $cache->method('isAvailable')->willReturn(false);
        $this->expectException(\FMUP\Cache\Exception::class);
        $this->expectExceptionMessage('Redis is not available');
        /** @var $cache Driver\Redis */
        $cache->remove('bob');
    }

    public function testIsAvailable()
    {
        $predisClient = $this->getMockBuilder(\Predis\Client::class)
            ->setMethods(array('ping'))
            ->getMock();
        $predisClient->method('ping')->willReturn(true);
        $cache = $this->getMockBuilder(\FMUP\Cache\Driver\Redis::class)
            ->setMethods(array('getRedisInstance'))
            ->getMock();
        $cache->method('getRedisInstance')->willReturn($predisClient);
        /** @var $cache Driver\Redis */
        $this->assertTrue($cache->isAvailable());
    }

    public function testIsAvailableWhenNotAvailable()
    {
        $predisClient = $this->getMockBuilder(\Predis\Client::class)
            ->setMethods(array('ping'))
            ->getMock();
        $predisClient->method('ping')->will($this->throwException(new \Exception));
        $cache = $this->getMockBuilder(\FMUP\Cache\Driver\Redis::class)
            ->setMethods(array('getRedisInstance'))
            ->getMock();
        $cache->method('getRedisInstance')->willReturn($predisClient);
        /** @var $cache Driver\Redis */
        $this->assertFalse($cache->isAvailable());
    }

    public function testHas()
    {
        $redis = $this->getMockBuilder(\Predis\Client::class)->setMethods(array('exists'))->getMock();
        $redis->method('exists')->willReturnOnConsecutiveCalls(true, true, false);

        $cache = $this->getMockBuilder(\FMUP\Cache\Driver\Redis::class)
            ->setMethods(array('isAvailable', 'getRedisInstance'))
            ->getMock();
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('getRedisInstance')->willReturn($redis);
        /** @var $cache Driver\Redis */
        $this->assertTrue($cache->has('test'));
        $this->assertTrue($cache->has('two'));
        $this->assertFalse($cache->has('notexist'));
    }

    public function testGet()
    {
        $redis = $this->getMockBuilder(\Predis\Client::class)->setMethods(array('get'))->getMock();
        $redis->method('get')->with($this->equalTo('test'))->willReturn(serialize('ok'));

        $cache = $this->getMockBuilder(\FMUP\Cache\Driver\Redis::class)
            ->setMethods(array('isAvailable', 'getRedisInstance'))
            ->getMock();
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('getRedisInstance')->willReturn($redis);
        /** @var $cache Driver\Redis */
        $this->assertSame('ok', $cache->get('test'));
    }

    public function testSet()
    {
        $redis = $this->getMockBuilder(\Predis\Client::class)
            ->setMethods(array('set', 'ttl', '__construct', 'expireAt'))
            ->getMock();
        $redis->method('set')
            ->with($this->equalTo('testKey'), $this->equalTo(serialize('testValue')))
            ->willReturn(true);
        $redis->method('expireAt')
            ->with($this->equalTo('testKey'), $this->equalTo(time()+20))
            ->willReturn(true);
        $redis->method('ttl')
            ->with($this->equalTo('testKey'))
            ->willReturn(true);

        $cache = $this->getMockBuilder(\FMUP\Cache\Driver\Redis::class)
            ->setMethods(array('isAvailable', 'getRedisInstance', 'getCacheKey', 'getSetting'))
            ->getMock();
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('getSetting')->with($this->equalTo(Driver\Redis::SETTINGS_TTL_IN_SECOND))->willReturn(20);
        $cache->method('getRedisInstance')->willReturn($redis);
        $cache->method('getCacheKey')->with($this->equalTo('testKey'))->willReturn('testKey');
        /** @var $cache Driver\Redis */
        $this->assertSame($cache, $cache->set('testKey', 'testValue'));
    }

    public function testSetFailsWhenCannotSet()
    {
        $redis = $this->getMockBuilder(\Predis\Client::class)
            ->setMethods(array('set', 'ttl', '__construct', 'expireAt'))
            ->getMock();
        $redis->method('ttl')
            ->with($this->equalTo('testKey'))
            ->willReturn(true);
        $redis->method('expireAt')
            ->with($this->equalTo('testKey'), $this->equalTo(time()+20))
            ->willReturn(true);
        $redis->method('set')
            ->with($this->equalTo('testKey'), $this->equalTo(serialize('testValue')))
            ->willReturn(false);

        $cache = $this->getMockBuilder(\FMUP\Cache\Driver\Redis::class)
            ->setMethods(array('isAvailable', 'getRedisInstance', 'getCacheKey', 'getSetting'))
            ->getMock();
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('getSetting')->with($this->equalTo(Driver\Redis::SETTINGS_TTL_IN_SECOND))->willReturn(20);
        $cache->method('getRedisInstance')->willReturn($redis);
        $cache->method('getCacheKey')->with($this->equalTo('testKey'))->willReturn('testKey');

        $this->expectException(\FMUP\Cache\Exception::class);
        $this->expectExceptionMessage('Error while inserting value in redis');
        /** @var $cache Driver\Redis */
        $cache->set('testKey', 'testValue');
    }
}
