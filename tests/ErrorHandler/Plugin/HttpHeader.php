<?php
/**
 * HttpHeader.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\ErrorHandler\Plugin;

use FMUP\Response\Header\Status;

class HttpHeaderTest extends \PHPUnit_Framework_TestCase
{
    public function testCanHandle()
    {
        $httpHeader = $this->getMockBuilder('\FMUP\ErrorHandler\Plugin\HttpHeader')
            ->setMethods(array('getException'))
            ->getMock();
        $httpHeader->expects($this->exactly(2))
            ->method('getException')
            ->will($this->onConsecutiveCalls(new \Exception, new \FMUP\Exception\Status\Unauthorized()));
        /** @var $httpHeader \FMUP\ErrorHandler\Plugin\HttpHeader */
        $this->assertTrue($httpHeader->canHandle());
        $this->assertFalse($httpHeader->canHandle());
    }

    public function testHandle()
    {
        $response = $this->getMockBuilder('\FMUP\Response')->setMethods(array('setHeader'))->getMock();
        $response->expects($this->exactly(1))
            ->method('setHeader')
            ->with($this->equalTo(new Status(Status::VALUE_INTERNAL_SERVER_ERROR)));
        $httpHeader = $this->getMockBuilder('\FMUP\ErrorHandler\Plugin\HttpHeader')
            ->setMethods(null)
            ->getMock();
        /**
         * @var $httpHeader \FMUP\ErrorHandler\Plugin\HttpHeader
         * @var $response \FMUP\Response
         */
        $this->assertSame($httpHeader, $httpHeader->setResponse($response)->handle());
    }
}
