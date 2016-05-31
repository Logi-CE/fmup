<?php
/**
 * OptionalTrait.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Sapi;

use FMUP\Sapi;

class MockSapi extends Sapi
{

}

class Mock implements Sapi\OptionalInterface
{
    use Sapi\OptionalTrait;
}

class OptionalTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testHasSetGet()
    {
        $mock = new Mock;
        $this->assertFalse($mock->hasSapi());
        $this->assertSame($mock, $mock->setSapi(MockSapi::getInstance()));
        $this->assertTrue($mock->hasSapi());
        $this->assertSame(MockSapi::getInstance(), $mock->getSapi());
        $this->assertSame($mock, $mock->setSapi());
        $this->assertFalse($mock->hasSapi());
        $this->assertSame(Sapi::getInstance(), $mock->getSapi());
        $this->assertTrue($mock->hasSapi());
    }
}
