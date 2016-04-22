<?php
namespace Tests\Cache\Driver;

use FMUP\Cache\Driver;

/**
 * @todo check if this work
 * @todo must test settings (TTL)
 */
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
        $cache = $this->getMock(Driver\Apc::class, array('apcStore', 'apcAdd', 'apcFetch', 'isAvailable'));
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('apcStore')->willReturn(true);
        $cache->method('apcFetch')->willReturn(true);
        $cache->method('apcAdd')->willReturn(true);
        /** @var \FMUP\Cache\Driver\Apc $cache */
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
            $return = $cache->set($case[0], $case[1]);
            $this->assertSame($cache, $return, 'Set settings must return its instance');
        }
        return $cache;
    }

    public function testSetWhenApcStoreFails()
    {
        $cache = $this->getMock(Driver\Apc::class, array('apcStore', 'apcAdd', 'apcFetch', 'isAvailable'));
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('apcStore')->willReturn(false);
        $cache->method('apcFetch')->willReturn(true);
        $cache->method('apcAdd')->willReturn(false);
        $this->setExpectedException(\FMUP\Cache\Exception::class, 'Unable to set key into cache APC');
        $cache->set('bob', 'bob');
    }

    public function testHasWhenApcNotAvailable()
    {
        $cache = $this->getMock(Driver\Apc::class, array('isAvailable'));
        $cache->method('isAvailable')->willReturn(false);
        $this->setExpectedException(\FMUP\Cache\Exception::class, 'APC is not available');
        /** @var $cache Driver\Apc */
        $cache->has('bob');
    }

    public function testGetWhenApcNotAvailable()
    {
        $cache = $this->getMock(Driver\Apc::class, array('isAvailable'));
        $cache->method('isAvailable')->willReturn(false);
        $this->setExpectedException(\FMUP\Cache\Exception::class, 'APC is not available');
        /** @var $cache Driver\Apc */
        $cache->get('bob');
    }

    public function testSetWhenApcNotAvailable()
    {
        $cache = $this->getMock(Driver\Apc::class, array('isAvailable'));
        $cache->method('isAvailable')->willReturn(false);
        $this->setExpectedException(\FMUP\Cache\Exception::class, 'APC is not available');
        /** @var $cache Driver\Apc */
        $cache->set('bob', 'bob');
    }

    public function testRemoveWhenApcNotAvailable()
    {
        $cache = $this->getMock(Driver\Apc::class, array('isAvailable'));
        $cache->method('isAvailable')->willReturn(false);
        $this->setExpectedException(\FMUP\Cache\Exception::class, 'APC is not available');
        /** @var $cache Driver\Apc */
        $cache->remove('bob');
    }

    public function testClearWhenApcNotAvailable()
    {
        $cache = $this->getMock(Driver\Apc::class, array('isAvailable'));
        $cache->method('isAvailable')->willReturn(false);
        $this->setExpectedException(\FMUP\Cache\Exception::class, 'APC is not available');
        /** @var $cache Driver\Apc */
        $cache->clear();
    }

    public function testInfoWhenApcNotAvailable()
    {
        $cache = $this->getMock(Driver\Apc::class, array('isAvailable'));
        $cache->method('isAvailable')->willReturn(false);
        $this->setExpectedException(\FMUP\Cache\Exception::class, 'APC is not available');
        /** @var $cache Driver\Apc */
        $cache->info();
    }

    public function testHas()
    {
        $cache = $this->getMock(Driver\Apc::class, array('isAvailable', 'apcExists'));
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
            $hasCase = $cache->has($case[0]);
            $this->assertTrue(is_bool($hasCase), 'Return should be boolean');
            $this->assertSame($case[1], $hasCase, 'Assert case : ' . $case[0] . ' on ' . ($key + 1));
        }
    }

    public function testRemoveWhenCacheOpCodeFails()
    {
        $cache = $this->getMock(Driver\Apc::class, array('isAvailable', 'apcDeleteFile'));
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('apcDeleteFile')->willReturn(false);
        /** @var Driver\Apc $cache */
        $cache->setSetting(Driver\Apc::SETTING_CACHE_TYPE, Driver\Apc::CACHE_TYPE_OP_CODE);
        $this->setExpectedException(\FMUP\Cache\Exception::class, 'Unable to delete key from cache APC');
        $cache->remove('unitTest');
    }

    public function testRemoveWhenCacheFails()
    {
        $cache = $this->getMock(Driver\Apc::class, array('isAvailable', 'apcDelete'));
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('apcDelete')->willReturn(false);
        /** @var Driver\Apc $cache */
        $cache->setSetting(Driver\Apc::SETTING_CACHE_TYPE, Driver\Apc::CACHE_TYPE_USER);
        $this->setExpectedException(\FMUP\Cache\Exception::class, 'Unable to delete key from cache APC');
        $cache->remove('unitTest');
    }

    public function testRemoveWhenCacheOpCodeSucceedAndOpCacheByDefault()
    {
        $cache = $this->getMock(Driver\Apc::class, array('isAvailable', 'apcDeleteFile'));
        $cache->method('isAvailable')->willReturn(true);
        $cache->expects($this->atLeastOnce())->method('apcDeleteFile')->willReturn(true);
        /** @var Driver\Apc $cache */
        $this->assertSame($cache, $cache->remove('unitTest'));
    }

    public function testRemoveWhenCacheSucceed()
    {
        $cache = $this->getMock(Driver\Apc::class, array('isAvailable', 'apcDelete'));
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('apcDelete')->willReturn(true);
        /** @var Driver\Apc $cache */
        $cache->setSetting(Driver\Apc::SETTING_CACHE_TYPE, Driver\Apc::CACHE_TYPE_USER);
        $this->assertSame($cache, $cache->remove('unitTest'));
    }

    public function testIsAvailable()
    {
        $cache = $this->getMock(Driver\Apc::class, null);
        $this->assertTrue(is_bool($cache->isAvailable()));
    }

    public function testClearFails()
    {
        $cache = $this->getMock(Driver\Apc::class, array('isAvailable', 'apcClearCache'));
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('apcClearCache')->willReturn(false);
        /** @var Driver\Apc $cache */
        $this->assertFalse($cache->clear());
    }

    public function testClearSucceed()
    {
        $cache = $this->getMock(Driver\Apc::class, array('isAvailable', 'apcClearCache'));
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('apcClearCache')->willReturn(true);
        /** @var Driver\Apc $cache */
        $this->assertTrue($cache->clear());
    }

    public function testInfoSucceed()
    {
        $cache = $this->getMock(Driver\Apc::class, array('isAvailable', 'apcCacheInfo'));
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('apcCacheInfo')->willReturn(array());
        /** @var Driver\Apc $cache */
        $this->assertTrue(is_array($cache->info()));
    }

    public function testInfoFails()
    {
        $cache = $this->getMock(Driver\Apc::class, array('isAvailable', 'apcCacheInfo'));
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('apcCacheInfo')->willReturn(false);
        /** @var Driver\Apc $cache */
        $this->assertFalse($cache->info());
    }

    public function testGetFails()
    {
        $cache = $this->getMock(Driver\Apc::class, array('isAvailable', 'apcFetch'));
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('apcFetch')->will($this->returnCallback(function ($key, &$success) { $success = false; return false;}));
        $key = 'unitTest';
        $this->setExpectedException(\FMUP\Cache\Exception::class, 'Unable to get ' . $key . ' from APC');
        /** @var Driver\Apc $cache */
        $cache->get($key);
    }

    public function testGetSucceed()
    {
        $cache = $this->getMock(Driver\Apc::class, array('isAvailable', 'apcFetch'));
        $cache->method('isAvailable')->willReturn(true);
        $cache->method('apcFetch')->will($this->returnCallback(function ($key, &$success) { $success = true; return $key;}));
        $key = 'unitTest';
        /** @var Driver\Apc $cache */
        $this->assertSame($key, $cache->get($key));
    }
}
