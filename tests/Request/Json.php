<?php
/**
 * Json.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Request;


class JsonTest extends \PHPUnit_Framework_TestCase
{
    public function testIsHttp()
    {
        $request = new \FMUP\Request\Json;
        $this->assertInstanceOf(\FMUP\Request\Http::class, $request);
        $this->assertInstanceOf(\FMUP\Request\Json::class, $request);
    }

    public function testGet()
    {
        $username = uniqid();
        $request = $this->getMockBuilder(\FMUP\Request\Json::class)->setMethods(['getRequestContent'])->getMock();
        $request->method('getRequestContent')->willReturn('{"username": "' . $username . '"}');

        /* @var $request \FMUP\Request\Json */
        $this->assertSame($username, $request->get('username'));
        $this->assertSame(null, $request->get('password'));
        $this->assertSame($username, $request->get('username', 123));
        $this->assertSame(123, $request->get('password', 123));
    }

    public function testHas()
    {
        $username = uniqid();
        $request = $this->getMockBuilder(\FMUP\Request\Json::class)->setMethods(['getRequestContent'])->getMock();
        $request->method('getRequestContent')->willReturn('{"username": "' . $username . '"}');

        /* @var $request \FMUP\Request\Json */
        $this->assertTrue($request->has('username'));
        $this->assertFalse($request->has('password'));
    }
}
