<?php
/**
 * OptionalTrait.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Environment;

use FMUP\Environment;

class MockEnvironment extends Environment
{

}

class Mock implements Environment\OptionalInterface
{
    use Environment\OptionalTrait;
}

class OptionalTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testHasSetGet()
    {
        $mock = new Mock;
        $this->assertFalse($mock->hasEnvironment());
        $this->assertSame($mock, $mock->setEnvironment(MockEnvironment::getInstance()));
        $this->assertTrue($mock->hasEnvironment());
        $this->assertSame(MockEnvironment::getInstance(), $mock->getEnvironment());
        $this->assertSame($mock, $mock->setEnvironment());
        $this->assertFalse($mock->hasEnvironment());
        $this->assertSame(Environment::getInstance(), $mock->getEnvironment());
        $this->assertTrue($mock->hasEnvironment());
    }
}
