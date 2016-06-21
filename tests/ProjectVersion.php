<?php
/**
 * ProjectVersion.php
 * @author: jmoulin@castelis.com
 */

namespace Tests;

class ProjectVersionMock extends \FMUP\ProjectVersion
{
    public function __construct()
    {

    }
}

class ProjectVersionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetInstance()
    {
        $reflector = new \ReflectionClass('\FMUP\ProjectVersion');
        $method = $reflector->getMethod('__construct');
        $this->assertTrue($method->isPrivate(), 'Construct must be private');
        try {
            $reflector->getMethod('__clone')->invoke(\FMUP\ProjectVersion::getInstance());
            $this->fail('Clone must fail');
        } catch (\ReflectionException $e) {
            $this->assertEquals(
                'Trying to invoke private method FMUP\ProjectVersion::__clone() from scope ReflectionMethod',
                $e->getMessage()
            );
        }

        $version = \FMUP\ProjectVersion::getInstance();
        $this->assertInstanceOf('\FMUP\ProjectVersion', $version);
        $version2 = \FMUP\ProjectVersion::getInstance();
        $this->assertSame($version, $version2);
        return $version;
    }

    public function testGetWhenFileDontExists()
    {
        $projectVersion = $this->getMockBuilder('\Tests\ProjectVersionMock')->setMethods(array('getComposerPath'))->getMock();
        $projectVersion->method('getComposerPath')->willReturn('nonexistent_file');

        $reflection = new \ReflectionProperty('\FMUP\ProjectVersion', 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($projectVersion);

        $this->setExpectedException('\LogicException', "composer.json does not exist");
        $projectVersion->get();
    }

    public function testGetWhenStructureIsBad()
    {
        $projectVersion = $this->getMockBuilder('\Tests\ProjectVersionMock')->setMethods(array('getComposerPath'))->getMock();
        $projectVersion->method('getComposerPath')->willReturn(__FILE__);

        $reflection = new \ReflectionProperty('\FMUP\ProjectVersion', 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($projectVersion);

        $this->setExpectedException('\LogicException', 'composer.json invalid structure');
        /** @var $projectVersion \FMUP\ProjectVersion */
        $projectVersion->get();
    }

    public function testGetAndName()
    {
        $filePath = __DIR__ . DIRECTORY_SEPARATOR . '.files' . DIRECTORY_SEPARATOR . 'composer.json';
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $version = uniqid();
        $data = <<<COMPOSER
{
    "name":"ProjectTest$version",
    "version":"$version"
}
COMPOSER;
        file_put_contents($filePath, $data);
        $projectVersion = $this->getMockBuilder('\Tests\ProjectVersionMock')->setMethods(array('getComposerPath'))->getMock();
        $projectVersion->method('getComposerPath')->willReturn($filePath);

        $reflection = new \ReflectionProperty('\FMUP\ProjectVersion', 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($projectVersion);

        /** @var $projectVersion \FMUP\ProjectVersion */
        $this->assertSame('ProjectTest' . $version, $projectVersion->name());
        $this->assertSame($version, $projectVersion->get());
        unlink($filePath);
    }
}
