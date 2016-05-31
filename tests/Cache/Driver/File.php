<?php
namespace Tests\Cache\Driver;

use FMUP\Cache\Driver;

class FileTest extends \PHPUnit_Framework_TestCase
{
    const TMP_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'test';

    public static function setUpBeforeClass()
    {
        mkdir(self::TMP_DIR);
    }

    public function testConstruct()
    {
        $cache = new \FMUP\Cache\Driver\File();
        $this->assertInstanceOf(\FMUP\Cache\CacheInterface::class, $cache, 'Instance of ' . \FMUP\Cache\CacheInterface::class);
        $this->assertInstanceOf(\FMUP\Cache\Driver\File::class, $cache, 'Instance of ' . \FMUP\Cache\Driver\File::class);
        $cache2 = new \FMUP\Cache\Driver\File(array(\FMUP\Cache\Driver\File::SETTING_SERIALIZE => true));
        $this->assertNotSame($cache2, $cache, 'New cache instance must not be same');
        $cache2->setSetting(\FMUP\Cache\Driver\File::SETTING_PATH, self::TMP_DIR);
        $this->assertSame(
            self::TMP_DIR,
            $cache2->getSetting(\FMUP\Cache\Driver\File::SETTING_PATH),
            'Settings path must be set by setSetting'
        );
        return $cache2;
    }

    /**
     * @depends testConstruct
     * @param \FMUP\Cache\Driver\File $cache
     * @return \FMUP\Cache
     */
    public function testSetGet(\FMUP\Cache\Driver\File $cache)
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
            array('1', $this->getMockBuilder(\stdClass::class)->getMock()),
        );
        $return = null;
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
     * @param \FMUP\Cache\Driver\File $cache
     */
    public function testHas(\FMUP\Cache\Driver\File $cache)
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
     * @param \FMUP\Cache\Driver\File $cache
     */
    public function testRemove(\FMUP\Cache\Driver\File $cache)
    {
        $this->assertTrue($cache->has('test'), 'Test should exist');
        $return = $cache->remove('test');
        $this->assertSame($cache, $return, 'Set settings must return its instance');
        $this->assertFalse($cache->has('test'), 'Test should\'nt exist');
    }

    /**
     * @depends testSetGet
     * @param \FMUP\Cache\Driver\File $cache
     * @return \FMUP\Cache\Driver\File
     */
    public function testUnserialize(\FMUP\Cache\Driver\File $cache)
    {
        $cache->setSetting(\FMUP\Cache\Driver\File::SETTING_SERIALIZE, false);
        $test = array(
            array('test', 'test'),
            array('test', 'bob'),
            array('bob', 'bob'),
            array('bob', 'test'),
            array('bob', 1),
            array('bob', '1'),
            array('1', '1'),
            array('1', '2'),
        );
        $return = null;
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

    public function testWhenUnableToCreateDirectory()
    {
        $cache = $this->getMock(Driver\File::class, array('mkDir', 'fileExists'));
        $cache->method('mkDir')->willReturn(false);
        $cache->method('fileExists')->willReturn(false);
        $this->setExpectedException(\FMUP\Cache\Exception::class);
        /** @var $cache Driver\File */
        $cache->setSetting(\FMUP\Cache\Driver\File::SETTING_PATH, null)->set('test', 1);
    }

    /**
     * @depends testSetGet
     * @param \FMUP\Cache\Driver\File $cache
     * @return \FMUP\Cache\Driver\File
     */
    public function testGetPathByKey(\FMUP\Cache\Driver\File $cache)
    {
        $dirs = array(
            self::TMP_DIR . DIRECTORY_SEPARATOR . uniqid(),
        );
        foreach ($dirs as $dir) {
            $cache->setSetting(\FMUP\Cache\Driver\File::SETTING_PATH, $dir);
            $test = array(
                array('test', 'test'),
                array('test', 'bob'),
                array('bob', 'bob'),
                array('bob', 'test'),
                array('bob', 1),
                array('bob', '1'),
                array('1', '1'),
                array('1', '2'),
            );
            $return = null;
            foreach ($test as $case) {
                try {
                    $return = $cache->set($case[0], $case[1]);
                } catch (\FMUP\Cache\Exception $e) {
                    $this->assertTrue(false, 'Unable to store ' . $case[1] . ' in ' . $case[0] . ' : ' . $e->getMessage());
                }
                $this->assertEquals($case[1], $cache->get($case[0]), 'Value is different on get');
                $this->assertSame($cache, $return, 'Set settings must return its instance');
            }
        }
        return $cache;
    }

    /**
     * @depends testSetGet
     * @param \FMUP\Cache\Driver\File $cache
     * @return \FMUP\Cache\Driver\File
     */
    public function testMkdir(\FMUP\Cache\Driver\File $cache)
    {
        $folder = self::TMP_DIR;
        $cache->setSetting(\FMUP\Cache\Driver\File::SETTING_PATH, $folder);
        $test = array(
            array('test', 'test'),
            array('test', 'bob'),
            array('bob', 'bob'),
            array('bob', 'test'),
            array('bob', 1),
            array('bob', '1'),
            array('1', '1'),
            array('1', '2'),
        );
        $return = null;
        foreach ($test as $case) {
            try {
                $return = $cache->set($case[0], $case[1]);
            } catch (\FMUP\Cache\Exception $e) {
                $this->assertEquals('Error while trying to create cache folder ' . $folder, $e->getMessage());
            }
            $this->assertEquals($case[1], $cache->get($case[0]), 'Value is different on get');
            $this->assertSame($cache, $return, 'Set settings must return its instance');
        }

        return $cache;
    }

    public static function tearDownAfterClass()
    {
        $dir = self::TMP_DIR;
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileInfo) {
            $todo = ($fileInfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileInfo->getRealPath());
        }

        rmdir($dir);
    }
}
