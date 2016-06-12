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
        $this->setExpectedException('\FMUP\Config\Exception', 'Config must be defined');
        $mock->getConfig();
    }

    public function testSetGetHasConfig()
    {
        $config = $this->getMockBuilder('\FMUP\Config\ConfigInterface')
            ->setMethods(array('get', 'set', 'mergeConfig', 'has'))
            ->getMock();
        /** @var $config \FMUP\Config\ConfigInterface */
        $mock = new RequiredTraitMock;
        $this->assertFalse($mock->hasConfig());
        $this->assertSame($mock, $mock->setConfig($config));
        $this->assertTrue($mock->hasConfig());
        $this->assertInstanceOf('\FMUP\Config\ConfigInterface', $mock->getConfig());
        $this->assertSame($config, $mock->getConfig());
    }
}
