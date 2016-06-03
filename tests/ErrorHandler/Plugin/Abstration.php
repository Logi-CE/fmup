<?php
/**
 * Abstration.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\ErrorHandler\Plugin;

class AbstrationTest extends \PHPUnit_Framework_TestCase
{

    public function testGetBootstrapWhenNotSet()
    {
        $abstraction = $this->getMockBuilder(\FMUP\ErrorHandler\Plugin\Abstraction::class)
            ->setMethods(array('handle', 'canHandle'))
            ->getMock();
        /** @var $abstraction \FMUP\ErrorHandler\Plugin\Abstraction */
        $this->expectException(\FMUP\Exception::class);
        $this->expectExceptionMessage('Unable to access bootstrap. Not set');
        $abstraction->getBootstrap();
    }

    public function testSetGetBootstrap()
    {
        $abstraction = $this->getMockBuilder(\FMUP\ErrorHandler\Plugin\Abstraction::class)
            ->setMethods(array('handle', 'canHandle'))
            ->getMock();
        $bootstrap = $this->getMockBuilder(\FMUP\Bootstrap::class)->getMock();
        /**
         * @var $abstraction \FMUP\ErrorHandler\Plugin\Abstraction
         * @var $bootstrap \FMUP\Bootstrap
         */
        $this->assertSame($abstraction, $abstraction->setBootstrap($bootstrap));
        $this->assertSame($bootstrap, $abstraction->getBootstrap());
    }

    public function testGetResponseWhenNotSet()
    {
        $abstraction = $this->getMockBuilder(\FMUP\ErrorHandler\Plugin\Abstraction::class)
            ->setMethods(array('handle', 'canHandle'))
            ->getMock();
        /** @var $abstraction \FMUP\ErrorHandler\Plugin\Abstraction */
        $this->expectException(\FMUP\Exception::class);
        $this->expectExceptionMessage('Unable to access response. Not set');
        $abstraction->getResponse();
    }

    public function testSetGetResponse()
    {
        $abstraction = $this->getMockBuilder(\FMUP\ErrorHandler\Plugin\Abstraction::class)
            ->setMethods(array('handle', 'canHandle'))
            ->getMock();
        $response = $this->getMockBuilder(\FMUP\Response::class)->getMock();
        /**
         * @var $abstraction \FMUP\ErrorHandler\Plugin\Abstraction
         * @var $response \FMUP\Response
         */
        $this->assertSame($abstraction, $abstraction->setResponse($response));
        $this->assertSame($response, $abstraction->getResponse());
    }

    public function testGetRequestWhenNotSet()
    {
        $abstraction = $this->getMockBuilder(\FMUP\ErrorHandler\Plugin\Abstraction::class)
            ->setMethods(array('handle', 'canHandle'))
            ->getMock();
        /** @var $abstraction \FMUP\ErrorHandler\Plugin\Abstraction */
        $this->expectException(\FMUP\Exception::class);
        $this->expectExceptionMessage('Unable to access request. Not set');
        $abstraction->getRequest();
    }

    public function testSetGetRequest()
    {
        $abstraction = $this->getMockBuilder(\FMUP\ErrorHandler\Plugin\Abstraction::class)
            ->setMethods(array('handle', 'canHandle'))
            ->getMock();
        $request = $this->getMockBuilder(\FMUP\Request::class)->getMock();
        /**
         * @var $abstraction \FMUP\ErrorHandler\Plugin\Abstraction
         * @var $request \FMUP\Request
         */
        $this->assertSame($abstraction, $abstraction->setRequest($request));
        $this->assertSame($request, $abstraction->getRequest());
    }

    public function testSetGetException()
    {
        $abstraction = $this->getMockBuilder(\FMUP\ErrorHandler\Plugin\Abstraction::class)
            ->setMethods(array('handle', 'canHandle'))
            ->getMock();
        /**
         * @var $abstraction \FMUP\ErrorHandler\Plugin\Abstraction
         */
        $exception = new \Exception();
        $this->assertNull($abstraction->getException());
        $this->assertSame($abstraction, $abstraction->setException($exception));
        $this->assertSame($exception, $abstraction->getException());
    }
}
