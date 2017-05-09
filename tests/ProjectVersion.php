<?php
/**
 * ProjectVersion.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests;

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
        $reflector = new \ReflectionClass(\FMUP\ProjectVersion::class);
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
        $this->assertInstanceOf(\FMUP\ProjectVersion::class, $version);
        $version2 = \FMUP\ProjectVersion::getInstance();
        $this->assertSame($version, $version2);
        return $version;
    }

    public function testComposerPath()
    {
        $reflection = new \ReflectionMethod(\FMUP\ProjectVersion::class, 'getComposerPath');
        $reflection->setAccessible(true);

        $projectVersion = $this->getMockBuilder(\FMUPTests\ProjectVersionMock::class)->setMethods(null)->getMock();
        $this->assertRegExp('~/../../../../composer.json$~', $reflection->invoke($projectVersion));
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
        $projectVersion = $this->getMockBuilder(ProjectVersionMock::class)->setMethods(array('getComposerPath'))->getMock();
        $projectVersion->method('getComposerPath')->willReturn($filePath);

        $reflection = new \ReflectionProperty(\FMUP\ProjectVersion::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($projectVersion);

        /** @var $projectVersion \FMUP\ProjectVersion */
        $this->assertSame('ProjectTest' . $version, $projectVersion->name());
        $this->assertSame($version, $projectVersion->get());
        unlink($filePath);
    }

    public function testGetVersionWhenEnvironmentSet()
    {
        $version = 'thisisascamtest';
        $projectVersion = $this->getMockBuilder(ProjectVersionMock::class)->setMethods(array('getEnv'))->getMock();
        $projectVersion->method('getEnv')->willReturn($version);
        /** @var $projectVersion \FMUP\ProjectVersion */
        $this->assertSame($version, $projectVersion->get());
    }

    public function testGetVersionWhenGitExists()
    {
        $projectVersion = $this->getMockBuilder(ProjectVersionMock::class)
            ->setMethods(array('getFromGit', 'getStructure'))
            ->getMock();
        $projectVersion->method('getFromGit')->willReturn("8.8.8\n");
        $projectVersion->method('getStructure')->will($this->throwException(new \LogicException));
        /** @var $projectVersion \FMUP\ProjectVersion */
        $this->assertSame('8.8.8', $projectVersion->get());
    }

    public function testGetVersionWhenNothingExists()
    {
        $projectVersion = $this->getMockBuilder(ProjectVersionMock::class)
            ->setMethods(array('getFromGit', 'getStructure'))
            ->getMock();
        $projectVersion
            ->method('getFromGit')
            ->willReturn("");
        $projectVersion
            ->method('getStructure')
            ->will($this->throwException(new \LogicException));
        /** @var $projectVersion \FMUP\ProjectVersion */
        $this->assertSame('v0.0.0', $projectVersion->get());
    }

    public function testGetWhenFileDontExists()
    {
        $projectVersion = $this->getMockBuilder(ProjectVersionMock::class)
            ->setMethods(array('getFromGit', 'getComposerPath'))
            ->getMock();
        $projectVersion
            ->method('getComposerPath')
            ->willReturn(implode(DIRECTORY_SEPARATOR, array(__DIR__, '.files', 'GITHEADnotexists')));
        $projectVersion->method('getFromGit')->willReturn("");
        /** @var $projectVersion \FMUP\ProjectVersion */
        $this->assertSame('v0.0.0', $projectVersion->get());
    }

    public function testGetWhenStructureIsBad()
    {
        $projectVersion = $this->getMockBuilder(ProjectVersionMock::class)
            ->setMethods(array('getFromGit', 'getComposerPath'))
            ->getMock();
        $projectVersion->method('getComposerPath')->willReturn(implode(DIRECTORY_SEPARATOR, array(__DIR__, '.files', 'GITHEAD')));
        $projectVersion->method('getFromGit')->willReturn('');
        /** @var $projectVersion \FMUP\ProjectVersion */
        $this->assertSame('v0.0.0', $projectVersion->get());
    }
}
