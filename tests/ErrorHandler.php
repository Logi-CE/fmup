<?php
/**
 * ErrorHandler.php
 * @author: jmoulin@castelis.com
 */

namespace Tests;

class ErrorHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testAddClearGet()
    {
        $errorHandler = new \FMUP\ErrorHandler();
        $plugin1 = $this->getMockBuilder(\FMUP\ErrorHandler\Plugin\Abstraction::class)
            ->setMethods(array('handle', 'canHandle'))
            ->getMock();
        $plugin2 = $this->getMockBuilder(\FMUP\ErrorHandler\Plugin\Abstraction::class)
            ->setMethods(array('handle', 'canHandle'))
            ->getMock();
        $plugin3 = $this->getMockBuilder(\FMUP\ErrorHandler\Plugin\Abstraction::class)
            ->setMethods(array('handle', 'canHandle'))
            ->getMock();
        /**
         * @var $plugin1 \FMUP\ErrorHandler\Plugin\Abstraction
         * @var $plugin2 \FMUP\ErrorHandler\Plugin\Abstraction
         * @var $plugin3 \FMUP\ErrorHandler\Plugin\Abstraction
         */
        $array = array(
            $plugin1,
            $plugin2,
            $plugin3,
        );
        $this->assertSame($errorHandler, $errorHandler->add($plugin2));
        $this->assertSame($errorHandler, $errorHandler->add($plugin3, \FMUP\ErrorHandler::WAY_APPEND));
        $this->assertSame($errorHandler, $errorHandler->add($plugin1, \FMUP\ErrorHandler::WAY_PREPEND));
        $this->assertSame($array, $errorHandler->get());
        $this->assertSame($errorHandler, $errorHandler->clear());
        $this->assertSame(array(), $errorHandler->get());
    }

    public function testGetResponseWhenNoResponse()
    {
        $errorHandler = new \FMUP\ErrorHandler();
        $this->expectException(\FMUP\Exception::class);
        $this->expectExceptionMessage('Unable to access response. Not set');
        $errorHandler->getResponse();
    }

    public function testSetGetResponse()
    {
        $response = $this->getMockBuilder(\FMUP\Response::class)->getMock();
        $errorHandler = new \FMUP\ErrorHandler();
        /** @var $response \FMUP\Response */
        $this->assertSame($errorHandler, $errorHandler->setResponse($response));
        $this->assertSame($response, $errorHandler->getResponse());
    }

    public function testGetRequestWhenNoRequest()
    {
        $errorHandler = new \FMUP\ErrorHandler();
        $this->expectException(\FMUP\Exception::class);
        $this->expectExceptionMessage('Unable to access request. Not set');
        $errorHandler->getRequest();
    }

    public function testSetGetRequest()
    {
        $request = $this->getMockBuilder(\FMUP\Request::class)->getMock();
        $errorHandler = new \FMUP\ErrorHandler();
        /** @var $request \FMUP\Request */
        $this->assertSame($errorHandler, $errorHandler->setRequest($request));
        $this->assertSame($request, $errorHandler->getRequest());
    }

    public function testGetBootstrapWhenNoBootstrap()
    {
        $errorHandler = new \FMUP\ErrorHandler();
        $this->expectException(\FMUP\Exception::class);
        $this->expectExceptionMessage('Unable to access bootstrap. Not set');
        $errorHandler->getBootstrap();
    }

    public function testSetGetBootstrap()
    {
        $bootstrap = $this->getMockBuilder(\FMUP\Bootstrap::class)->getMock();
        $errorHandler = new \FMUP\ErrorHandler();
        /** @var $bootstrap \FMUP\Bootstrap */
        $this->assertSame($errorHandler, $errorHandler->setBootstrap($bootstrap));
        $this->assertSame($bootstrap, $errorHandler->getBootstrap());
    }

    public function testHandleWhenNoPlugin()
    {
        $errorHandler = new \FMUP\ErrorHandler();
        $this->expectException(\Exception::class);
        $errorHandler->handle(new \Exception);
    }

    public function testHandle()
    {
        $bootstrap = $this->getMockBuilder(\FMUP\Bootstrap::class)->getMock();
        $request = $this->getMockBuilder(\FMUP\Request::class)->getMock();
        $response = $this->getMockBuilder(\FMUP\Response::class)->getMock();
        /**
         * @var $response \FMUP\Response
         * @var $request \FMUP\Request
         * @var $bootstrap \FMUP\Bootstrap
         */
        $errorHandler = new \FMUP\ErrorHandler;
        $plugin1 = $this->getMockBuilder(\FMUP\ErrorHandler\Plugin\Abstraction::class)
            ->setMethods(array('handle', 'canHandle'))
            ->getMock();
        $plugin1->method('canHandle')->willReturn(true);
        $plugin1->expects($this->exactly(1))->method('handle');
        $plugin2 = $this->getMockBuilder(\FMUP\ErrorHandler\Plugin\Abstraction::class)
            ->setMethods(array('handle', 'canHandle'))
            ->getMock();
        $plugin2->method('canHandle')->willReturn(false);
        $plugin2->expects($this->exactly(0))->method('handle');
        $plugin3 = $this->getMockBuilder(\FMUP\ErrorHandler\Plugin\Abstraction::class)
            ->setMethods(array('handle', 'canHandle'))
            ->getMock();
        $plugin3->method('canHandle')->willReturn(true);
        $plugin3->expects($this->exactly(1))->method('handle');

        /**
         * @var $plugin1 \FMUP\ErrorHandler\Plugin\Abstraction
         * @var $plugin2 \FMUP\ErrorHandler\Plugin\Abstraction
         * @var $plugin3 \FMUP\ErrorHandler\Plugin\Abstraction
         */
        $errorHandler->add($plugin2)
            ->add($plugin3, \FMUP\ErrorHandler::WAY_APPEND)
            ->add($plugin1, \FMUP\ErrorHandler::WAY_PREPEND);
        $this->assertSame(
            $errorHandler,
            $errorHandler->setBootstrap($bootstrap)
                ->setResponse($response)
                ->setRequest($request)
                ->handle(new \Exception)
        );
    }
}
