<?php
/**
 * Version.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests;

class VersionMock extends \FMUP\Version
{
    public function __construct()
    {

    }
}

class VersionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetInstance()
    {
        $reflector = new \ReflectionClass(\FMUP\Version::class);
        $method = $reflector->getMethod('__construct');
        $this->assertTrue($method->isPrivate(), 'Construct must be private');
        try {
            $reflector->getMethod('__clone')->invoke(\FMUP\Version::getInstance());
            $this->fail('Clone must fail');
        } catch (\ReflectionException $e) {
            $this->assertEquals(
                'Trying to invoke private method FMUP\Version::__clone() from scope ReflectionMethod',
                $e->getMessage()
            );
        }

        $version = \FMUP\Version::getInstance();
        $this->assertInstanceOf(\FMUP\Version::class, $version);
        $version2 = \FMUP\Version::getInstance();
        $this->assertSame($version, $version2);
    }

    public function testGet()
    {
        $file = implode(DIRECTORY_SEPARATOR, array(__DIR__ , '.files', 'composer.lock'));
        $versionMock = $this->getMockBuilder(VersionMock::class)->setMethods(array('getComposerPath'))->getMock();
        $versionMock->method('getComposerPath')->willReturn($file);
        /** @var $version \FMUP\Version */
        $this->assertSame($versionMock->get(), '10.0.1');
    }

    public function testGetWhenFilePathFails()
    {
        $version = $this->getMockBuilder(VersionMock::class)->setMethods(array('getComposerPath'))->getMock();
        $version->method('getComposerPath')->willReturn('/unexistingFile');

        $reflection = new \ReflectionProperty(\FMUP\Version::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue(\FMUP\Version::getInstance(), $version);

        $this->expectException(\FMUP\Exception::class);
        $this->expectExceptionMessage('composer.lock does not exist');
        /** @var $version \FMUP\Version */
        $version->get();
    }

    public function testGetWhenFileIsNotValid()
    {
        $version = $this->getMockBuilder(VersionMock::class)->setMethods(array('getComposerPath'))->getMock();
        $version->method('getComposerPath')->willReturn(__FILE__);

        $reflection = new \ReflectionProperty(\FMUP\Version::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue(\FMUP\Version::getInstance(), $version);

        $this->expectException(\FMUP\Exception::class);
        $this->expectExceptionMessage('composer.lock invalid structure');
        /** @var $version \FMUP\Version */
        $version->get();
    }
}
