<?php
/**
 * Response.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests;

class ResponseHeaderMockResponse extends \FMUP\Response\Header
{
    public function getType()
    {
    }
}

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $response = new \FMUP\Response();
        $response2 = clone $response;
        $this->assertNotSame($response, $response2);
    }

    public function testAddHeader()
    {
        $response = new \FMUP\Response;
        $header1 = $this->getMockBuilder(ResponseHeaderMockResponse::class)->setMethods(array('getType'))->getMock();
        $header1->method('getType')->willReturn('type');
        $header2 = $this->getMockBuilder(ResponseHeaderMockResponse::class)->setMethods(array('getType'))->getMock();
        $header2->method('getType')->willReturn('type2');
        $header3 = $this->getMockBuilder(ResponseHeaderMockResponse::class)->setMethods(array('getType'))->getMock();
        $header3->method('getType')->willReturn('type');

        /**
         * @var $header1 \FMUP\Response\Header
         * @var $header2 \FMUP\Response\Header
         * @var $header3 \FMUP\Response\Header
         */
        $this->assertSame($response, $response->addHeader($header1));
        $response->addHeader($header2)->addHeader($header3);
        $expectedHeaders = array(
            $header1->getType() => array(
                $header1,
                $header3,
            ),
            $header2->getType() => array(
                $header2,
            )
        );
        $this->assertSame($expectedHeaders, $response->getHeaders());
    }

    public function testClearHeader()
    {
        $response = new \FMUP\Response;
        $header1 = $this->getMockBuilder(ResponseHeaderMockResponse::class)->setMethods(array('getType'))->getMock();
        $header1->method('getType')->willReturn('type');
        $header2 = $this->getMockBuilder(ResponseHeaderMockResponse::class)->setMethods(array('getType'))->getMock();
        $header2->method('getType')->willReturn('type2');
        $header3 = $this->getMockBuilder(ResponseHeaderMockResponse::class)->setMethods(array('getType'))->getMock();
        $header3->method('getType')->willReturn('type');

        /**
         * @var $header1 \FMUP\Response\Header
         * @var $header2 \FMUP\Response\Header
         * @var $header3 \FMUP\Response\Header
         */
        $response->addHeader($header1)->addHeader($header2)->addHeader($header3);
        $this->assertSame($response, $response->clearHeader());
        $this->assertSame(array(), $response->getHeaders());
        $response->addHeader($header1)->addHeader($header2)->addHeader($header3);
        $this->assertSame($response, $response->clearHeader('type'));
        $expected = array(
            $header2->getType() => array(
                $header2,
            ),
        );
        $this->assertSame($expected, $response->getHeaders());
    }

    public function testGetSetBody()
    {
        $response = new \FMUP\Response();
        $this->assertSame($response, $response->setBody('123456'));
        $this->assertSame('123456', $response->getBody());
    }

    public function testGetSetReturnCode()
    {
        $response = new \FMUP\Response();
        $this->assertSame(0, $response->getReturnCode());
        $this->assertSame(1, $response->setReturnCode(1)->getReturnCode());
        $this->assertSame(1, $response->setReturnCode(1.56)->getReturnCode());
    }

    public function testSend()
    {
        $response = new \FMUP\Response;
        $header1 = $this->getMockBuilder(ResponseHeaderMockResponse::class)->setMethods(array('render', 'getType'))->getMock();
        $header1->method('render')->will($this->returnCallback(function () { echo 'header1';}));
        $header1->method('getType')->willReturn('type1');
        $header2 = $this->getMockBuilder(ResponseHeaderMockResponse::class)->setMethods(array('render', 'getType'))->getMock();
        $header2->method('render')->will($this->returnCallback(function () { echo 'header2';}));
        $header2->method('getType')->willReturn('type2');
        $header3 = $this->getMockBuilder(ResponseHeaderMockResponse::class)->setMethods(array('render', 'getType'))->getMock();
        $header3->method('render')->will($this->returnCallback(function () { echo 'header3';}));
        $header3->method('getType')->willReturn('type1');

        /**
         * @var $header1 \FMUP\Response\Header
         * @var $header2 \FMUP\Response\Header
         * @var $header3 \FMUP\Response\Header
         */
        $this->expectOutputString('header1header3header2body');
        $response->addHeader($header1)->addHeader($header2)->addHeader($header3)->setBody('body')->send();
    }

    public function testSendWithReturnCode()
    {
        $response = $this->getMockBuilder(\FMUP\Response::class)->setMethods(array('exitPhp'))->getMock();
        $response->expects($this->exactly(1))->method('exitPhp')->with($this->equalTo(1));

        /** @var $response \FMUP\Response */
        $response->setReturnCode(1)->send();
    }
}
