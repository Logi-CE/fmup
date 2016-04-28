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
        $abstraction = $this->getMock(\FMUP\ErrorHandler\Plugin\Abstraction::class, array('handle', 'canHandle'));
        /** @var $abstraction \FMUP\ErrorHandler\Plugin\Abstraction */
        $this->expectException(\FMUP\Exception::class);
        $this->expectExceptionMessage('Unable to access bootstrap. Not set');
        $abstraction->getBootstrap();
    }

    public function testSetGetBootstrap()
    {
        $abstraction = $this->getMock(\FMUP\ErrorHandler\Plugin\Abstraction::class, array('handle', 'canHandle'));
        $bootstrap = $this->getMock(\FMUP\Bootstrap::class);
        /**
         * @var $abstraction \FMUP\ErrorHandler\Plugin\Abstraction
         * @var $bootstrap \FMUP\Bootstrap
         */
        $this->assertSame($abstraction, $abstraction->setBootstrap($bootstrap));
        $this->assertSame($bootstrap, $abstraction->getBootstrap());
    }

    public function testGetResponseWhenNotSet()
    {
        $abstraction = $this->getMock(\FMUP\ErrorHandler\Plugin\Abstraction::class, array('handle', 'canHandle'));
        /** @var $abstraction \FMUP\ErrorHandler\Plugin\Abstraction */
        $this->expectException(\FMUP\Exception::class);
        $this->expectExceptionMessage('Unable to access response. Not set');
        $abstraction->getResponse();
    }

    public function testSetGetResponse()
    {
        $abstraction = $this->getMock(\FMUP\ErrorHandler\Plugin\Abstraction::class, array('handle', 'canHandle'));
        $response = $this->getMock(\FMUP\Response::class);
        /**
         * @var $abstraction \FMUP\ErrorHandler\Plugin\Abstraction
         * @var $response \FMUP\Response
         */
        $this->assertSame($abstraction, $abstraction->setResponse($response));
        $this->assertSame($response, $abstraction->getResponse());
    }

    public function testGetRequestWhenNotSet()
    {
        $abstraction = $this->getMock(\FMUP\ErrorHandler\Plugin\Abstraction::class, array('handle', 'canHandle'));
        /** @var $abstraction \FMUP\ErrorHandler\Plugin\Abstraction */
        $this->expectException(\FMUP\Exception::class);
        $this->expectExceptionMessage('Unable to access request. Not set');
        $abstraction->getRequest();
    }

    public function testSetGetRequest()
    {
        $abstraction = $this->getMock(\FMUP\ErrorHandler\Plugin\Abstraction::class, array('handle', 'canHandle'));
        $request = $this->getMock(\FMUP\Request::class);
        /**
         * @var $abstraction \FMUP\ErrorHandler\Plugin\Abstraction
         * @var $request \FMUP\Request
         */
        $this->assertSame($abstraction, $abstraction->setRequest($request));
        $this->assertSame($request, $abstraction->getRequest());
    }

    public function testSetGetException()
    {
        $abstraction = $this->getMock(\FMUP\ErrorHandler\Plugin\Abstraction::class, array('handle', 'canHandle'));
        /**
         * @var $abstraction \FMUP\ErrorHandler\Plugin\Abstraction
         */
        $exception = new \Exception();
        $this->assertNull($abstraction->getException());
        $this->assertSame($abstraction, $abstraction->setException($exception));
        $this->assertSame($exception, $abstraction->getException());
    }
}
