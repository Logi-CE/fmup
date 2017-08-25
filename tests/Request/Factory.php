<?php
namespace FMUPTests\Request;

class SapiMockFactory extends \FMUP\Sapi
{
    public function __construct()
    {

    }
}
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetWhenHttp()
    {
        $sapi = $this->getMockBuilder(SapiMockFactory::class)->setMethods(array('getRaw'))->getMock();
        $sapi->method('getRaw')->willReturn(SapiMockFactory::CGI);

        $factory = $this->getMockBuilder(\FMUP\Request\Factory::class)->setMethods(['getSapi', 'getHeaders'])->getMock();
        $factory->method('getSapi')->willReturn($sapi);
        $factory->method('getHeaders')->willReturn(['Content-Type' => 'application/html']);

        /* @var $factory \FMUP\Request\Factory */
        $instance = $factory->get();
        $this->assertInstanceOf(\FMUP\Request\Http::class, $instance);
        $this->assertNotSame($instance, $factory->get());
    }

    public function testGetWhenJson()
    {
        $sapi = $this->getMockBuilder(SapiMockFactory::class)->setMethods(array('getRaw'))->getMock();
        $sapi->method('getRaw')->willReturn(SapiMockFactory::CGI);

        $factory = $this->getMockBuilder(\FMUP\Request\Factory::class)->setMethods(['getSapi', 'getHeaders'])->getMock();
        $factory->method('getSapi')->willReturn($sapi);
        $factory->method('getHeaders')->willReturn(['Content-Type' => 'application/json']);

        /* @var $factory \FMUP\Request\Factory */
        $instance = $factory->get();
        $this->assertInstanceOf(\FMUP\Request\Json::class, $instance);
        $this->assertNotSame($instance, $factory->get());
    }

    public function testGetWhenCli()
    {
        $sapi = $this->getMockBuilder(SapiMockFactory::class)->setMethods(array('getRaw'))->getMock();
        $sapi->method('getRaw')->willReturn(SapiMockFactory::CLI);

        $factory = $this->getMockBuilder(\FMUP\Request\Factory::class)->setMethods(array('getSapi'))->getMock();
        $factory->method('getSapi')->willReturn($sapi);

        /* @var $factory \FMUP\Request\Factory */
        $instance = $factory->get();
        $this->assertInstanceOf(\FMUP\Request\Cli::class, $instance);
        $this->assertNotSame($instance, $factory->get());
    }
}
