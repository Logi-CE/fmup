<?php
/**
 * OptionalTrait.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Config;

class OptionalTraitMock
{
    use \FMUP\Config\OptionalTrait;
}

class OptionalTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGetHasConfig()
    {
        $config = $this->getMockBuilder(\FMUP\Config\ConfigInterface::class)
            ->setMethods(array('get', 'set', 'mergeConfig', 'has'))
            ->getMock();
        $mock = new OptionalTraitMock;
        $this->assertFalse($mock->hasConfig());
        $configDefault = $mock->getConfig();
        $this->assertInstanceOf(\FMUP\Config\ConfigInterface::class, $configDefault);
        $this->assertInstanceOf(\FMUP\Config::class, $configDefault);
        $this->assertTrue($mock->hasConfig());
        $this->assertSame($configDefault, $mock->getConfig());

        $mock = new OptionalTraitMock;
        $this->assertFalse($mock->hasConfig());
        $this->assertSame($mock, $mock->setConfig($config));
        $this->assertTrue($mock->hasConfig());
        $this->assertSame($config, $mock->getConfig());
    }
}
