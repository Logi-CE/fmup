<?php
/**
 * RequiredTrait.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Config;

class RequiredTraitMock
{
    use \FMUP\Config\RequiredTrait;
}

class RequiredTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConfigFail()
    {
        $mock = new RequiredTraitMock;
        $this->assertFalse($mock->hasConfig());
        $this->expectException(\FMUP\Config\Exception::class);
        $this->expectExceptionMessage('Config must be defined');
        $mock->getConfig();
    }

    public function testSetGetHasConfig()
    {
        $config = $this->getMock(\FMUP\Config\ConfigInterface::class, array('get', 'set', 'mergeConfig', 'has'));
        $mock = new RequiredTraitMock;
        $this->assertFalse($mock->hasConfig());
        $this->assertSame($mock, $mock->setConfig($config));
        $this->assertTrue($mock->hasConfig());
        $this->assertInstanceOf(\FMUP\Config\ConfigInterface::class, $mock->getConfig());
        $this->assertSame($config, $mock->getConfig());
    }
}
