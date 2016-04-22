<?php
/**
 * Sapi.php
 * @author: jmoulin@castelis.com
 */

namespace Tests;

use FMUP\Sapi;

if (!class_exists('\Tests\SapiMock')) {
    class SapiMock extends \FMUP\Sapi
    {
        public function __construct()
        {

        }
    }
}

class SapiTest extends \PHPUnit_Framework_TestCase
{
    public function testGetInstance()
    {
        $reflector = new \ReflectionClass(\FMUP\Sapi::class);
        $method = $reflector->getMethod('__construct');
        $this->assertTrue($method->isPrivate(), 'Construct must be private');
        try {
            $reflector->getMethod('__clone')->invoke(\FMUP\Sapi::getInstance());
            $this->fail('Clone must fail');
        } catch (\ReflectionException $e) {
            $this->assertEquals(
                'Trying to invoke private method FMUP\Sapi::__clone() from scope ReflectionMethod',
                $e->getMessage()
            );
        }

        $sapi = \FMUP\Sapi::getInstance();
        $this->assertInstanceOf(\FMUP\Sapi::class, $sapi);
        $sapi2 = \FMUP\Sapi::getInstance();
        $this->assertSame($sapi, $sapi2);
    }

    public function testGet()
    {
        $expectedValues = array(\FMUP\Sapi::CLI => 1, \FMUP\Sapi::CGI => 1);
        $this->assertTrue(isset($expectedValues[\FMUP\Sapi::getInstance()->get()]));
    }

    public function testIsCli()
    {
        $sapi = $this->getMock(SapiMock::class, array('getRaw'));
        $sapi->method('getRaw')->willReturn(SapiMock::CLI);

        $reflection = new \ReflectionProperty(\FMUP\Sapi::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($sapi);
        /** @var $sapi \FMUP\Sapi */
        $this->assertTrue($sapi->is(SapiMock::CLI));
    }
}
