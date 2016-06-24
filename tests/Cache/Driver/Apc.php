<?php
namespace FMUPTests\Cache\Driver;

use FMUP\Cache\Driver;

class ApcTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $cache = new Driver\Apc();
        $this->assertInstanceOf(\FMUP\Cache\CacheInterface::class, $cache, 'Instance of \FMUP\Cache\CacheInterface');
        $cache2 = new Driver\Apc(array(Driver\Apc::SETTING_CACHE_TYPE => Driver\Apc::CACHE_TYPE_USER));
        $this->assertNotSame($cache2, $cache, 'New cache instance must not be same');
        $this->assertNotEquals($cache2, $cache, 'New cache instance must not be equal');
        return $cache2;
    }

    public function testSet()
    {
        $cache = $this->getMockBuilder(Driver\Apc::class)
            ->setMethods(array('apcStore', 'apcAdd', 'apcFetch', 'isAvailable', 'getSetting', 'getCacheKey'))
            ->getMock();
        $cache->method('getSetting')
            ->with($this->equalTo(Driver\Apc::SETTING_CACHE_TTL))
            ->willReturnOnConsecutiveCalls(20, 20, 20, 20, 0);
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('apcStore')
            ->withConsecutive(
                array($this->equalTo('testKey'), $this->equalTo('testValue'), $this->equalTo(20)),
                array($this->equalTo('testKey'), $this->equalTo('testValue'), $this->equalTo(20)),
                array($this->equalTo('testKey'), $this->equalTo('testValue'), $this->equalTo(0))
            )
            ->willReturnOnConsecutiveCalls(true, false, true);
        $cache->expects($this->exactly(3))->method('getCacheKey')->with($this->equalTo('testKey'))->willReturn('testKey');
        $cache->expects($this->once())
            ->method('apcAdd')
            ->with($this->equalTo('testKey'), $this->equalTo('testValue'), $this->equalTo(20))
            ->willReturn(true);
        /** @var \FMUP\Cache\Driver\Apc $cache */
        $this->assertSame($cache, $cache->set('testKey', 'testValue'));
        $this->assertSame($cache, $cache->set('testKey', 'testValue'));
        $this->assertSame($cache, $cache->set('testKey', 'testValue'));
    }

    public function testSetWhenApcStoreFails()
    {
        $cache = $this->getMockBuilder(Driver\Apc::class)
            ->setMethods(array('apcStore', 'apcAdd', 'apcFetch', 'isAvailable'))
            ->getMock();
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('apcStore')->willReturn(false);
        $cache->method('apcFetch')->willReturn(true);
        $cache->method('apcAdd')->willReturn(false);
        $this->expectException(\FMUP\Cache\Exception::class);
        $this->expectExceptionMessage('Unable to set key into cache APC');
        /** @var $cache Driver\Apc */
        $cache->set('bob', 'bob');
    }

    public function testHasWhenApcNotAvailable()
    {
        $cache = $this->getMockBuilder(Driver\Apc::class)->setMethods(array('isAvailable'))->getMock();
        $cache->method('isAvailable')->willReturn(false);
        $this->expectException(\FMUP\Cache\Exception::class);
        $this->expectExceptionMessage('APC is not available');
        /** @var $cache Driver\Apc */
        $cache->has('bob');
    }

    public function testGetWhenApcNotAvailable()
    {
        $cache = $this->getMockBuilder(Driver\Apc::class)->setMethods(array('isAvailable'))->getMock();
        $cache->method('isAvailable')->willReturn(false);
        $this->expectException(\FMUP\Cache\Exception::class);
        $this->expectExceptionMessage('APC is not available');
        /** @var $cache Driver\Apc */
        $cache->get('bob');
    }

    public function testSetWhenApcNotAvailable()
    {
        $cache = $this->getMockBuilder(Driver\Apc::class)->setMethods(array('isAvailable'))->getMock();
        $cache->method('isAvailable')->willReturn(false);
        $this->expectException(\FMUP\Cache\Exception::class);
        $this->expectExceptionMessage('APC is not available');
        /** @var $cache Driver\Apc */
        $cache->set('bob', 'bob');
    }

    public function testRemoveWhenApcNotAvailable()
    {
        $cache = $this->getMockBuilder(Driver\Apc::class)->setMethods(array('isAvailable'))->getMock();
        $cache->method('isAvailable')->willReturn(false);
        $this->expectException(\FMUP\Cache\Exception::class);
        $this->expectExceptionMessage('APC is not available');
        /** @var $cache Driver\Apc */
        $cache->remove('bob');
    }

    public function testClearWhenApcNotAvailable()
    {
        $cache = $this->getMockBuilder(Driver\Apc::class)->setMethods(array('isAvailable'))->getMock();
        $cache->method('isAvailable')->willReturn(false);
        $this->expectException(\FMUP\Cache\Exception::class);
        $this->expectExceptionMessage('APC is not available');
        /** @var $cache Driver\Apc */
        $cache->clear();
    }

    public function testInfoWhenApcNotAvailable()
    {
        $cache = $this->getMockBuilder(Driver\Apc::class)->setMethods(array('isAvailable'))->getMock();
        $cache->method('isAvailable')->willReturn(false);
        $this->expectException(\FMUP\Cache\Exception::class);
        $this->expectExceptionMessage('APC is not available');
        /** @var $cache Driver\Apc */
        $cache->info();
    }

    public function testHas()
    {
        $cache = $this->getMockBuilder(Driver\Apc::class)->setMethods(array('isAvailable', 'apcExists'))->getMock();
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('apcExists')
            ->will($this->onConsecutiveCalls(false, false, false, false, false, true, true, true, true, true));
        $test = array(
            array('test', false),
            array('bob', false),
            array('1', false),
            array(1, false),
            array('notexists', false),
            array('test', true),
            array('bob', true),
            array('1', true),
            array(1, true),
            array('notexists', true),
        );
        foreach ($test as $key => $case) {
            /** @var $cache Driver\Apc */
            $hasCase = $cache->has($case[0]);
            $this->assertTrue(is_bool($hasCase), 'Return should be boolean');
            $this->assertSame($case[1], $hasCase, 'Assert case : ' . $case[0] . ' on ' . ($key + 1));
        }
    }

    public function testRemoveWhenCacheOpCodeFails()
    {
        $cache = $this->getMockBuilder(Driver\Apc::class)->setMethods(array('isAvailable', 'apcDeleteFile'))->getMock();
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('apcDeleteFile')->willReturn(false);
        /** @var Driver\Apc $cache */
        $cache->setSetting(Driver\Apc::SETTING_CACHE_TYPE, Driver\Apc::CACHE_TYPE_OP_CODE);
        $this->expectException(\FMUP\Cache\Exception::class);
        $this->expectExceptionMessage('Unable to delete key from cache APC');
        $cache->remove('unitTest');
    }

    public function testRemoveWhenCacheFails()
    {
        $cache = $this->getMockBuilder(Driver\Apc::class)->setMethods(array('isAvailable', 'apcDelete'))->getMock();
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('apcDelete')->willReturn(false);
        /** @var Driver\Apc $cache */
        $cache->setSetting(Driver\Apc::SETTING_CACHE_TYPE, Driver\Apc::CACHE_TYPE_USER);
        $this->expectException(\FMUP\Cache\Exception::class);
        $this->expectExceptionMessage('Unable to delete key from cache APC');
        $cache->remove('unitTest');
    }

    public function testRemoveWhenCacheOpCodeSucceedAndOpCacheByDefault()
    {
        $cache = $this->getMockBuilder(Driver\Apc::class)->setMethods(array('isAvailable', 'apcDeleteFile'))->getMock();
        $cache->method('isAvailable')->willReturn(true);
        $cache->expects($this->atLeastOnce())->method('apcDeleteFile')->willReturn(true);
        /** @var Driver\Apc $cache */
        $this->assertSame($cache, $cache->remove('unitTest'));
    }

    public function testRemoveWhenCacheSucceed()
    {
        $cache = $this->getMockBuilder(Driver\Apc::class)->setMethods(array('isAvailable', 'apcDelete'))->getMock();
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('apcDelete')->willReturn(true);
        /** @var Driver\Apc $cache */
        $cache->setSetting(Driver\Apc::SETTING_CACHE_TYPE, Driver\Apc::CACHE_TYPE_USER);
        $this->assertSame($cache, $cache->remove('unitTest'));
    }

    public function testIsAvailable()
    {
        $cache = $this->getMockBuilder(Driver\Apc::class)->setMethods(null)->getMock();
        /** @var $cache Driver\Apc */
        $this->assertTrue(is_bool($cache->isAvailable()));
    }

    public function testClearFails()
    {
        $cache = $this->getMockBuilder(Driver\Apc::class)->setMethods(array('isAvailable', 'apcClearCache'))->getMock();
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('apcClearCache')->willReturn(false);
        /** @var Driver\Apc $cache */
        $this->assertFalse($cache->clear());
    }

    public function testClearSucceed()
    {
        $cache = $this->getMockBuilder(Driver\Apc::class)->setMethods(array('isAvailable', 'apcClearCache'))->getMock();
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('apcClearCache')->willReturn(true);
        /** @var Driver\Apc $cache */
        $this->assertTrue($cache->clear());
    }

    public function testInfoSucceed()
    {
        $cache = $this->getMockBuilder(Driver\Apc::class)->setMethods(array('isAvailable', 'apcCacheInfo'))->getMock();
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('apcCacheInfo')->willReturn(array());
        /** @var Driver\Apc $cache */
        $this->assertTrue(is_array($cache->info()));
    }

    public function testInfoFails()
    {
        $cache = $this->getMockBuilder(Driver\Apc::class)->setMethods(array('isAvailable', 'apcCacheInfo'))->getMock();
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('apcCacheInfo')->willReturn(false);
        /** @var Driver\Apc $cache */
        $this->assertFalse($cache->info());
    }

    public function testGetFails()
    {
        $cache = $this->getMockBuilder(Driver\Apc::class)->setMethods(array('isAvailable', 'apcFetch'))->getMock();
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('apcFetch')
            ->will($this->returnCallback(function ($key, &$success) { $success = $key && false; return false;}));
        $key = 'unitTest';
        $this->expectException(\FMUP\Cache\Exception::class);
        $this->expectExceptionMessage('Unable to get ' . $key . ' from APC');
        /** @var Driver\Apc $cache */
        $cache->get($key);
    }

    public function testGetSucceed()
    {
        $cache = $this->getMockBuilder(Driver\Apc::class)->setMethods(array('isAvailable', 'apcFetch'))->getMock();
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('apcFetch')->will($this->returnCallback(function ($key, &$success) { $success = true; return $key;}));
        $key = 'unitTest';
        /** @var Driver\Apc $cache */
        $this->assertSame($key, $cache->get($key));
    }
}
