<?php
/**
 * ErrorController.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\ErrorHandler\Plugin;

class ErrorControllerMockErrorController extends \FMUP\ErrorHandler\Plugin\ErrorController
{
    public function __construct()
    {
    }
}

class ErrorControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $errorController = $this->getMockBuilder(\FMUP\Controller\Error::class)->setMethods(array('render'))->getMock();
        $errorController2 = $this->getMockBuilder(\FMUP\Controller\Error::class)
            ->setMethods(array('render'))
            ->getMock();
        /**
         * @var $errorController \FMUP\Controller\Error
         * @var $errorController2 \FMUP\Controller\Error
         */
        $errorPlugin = new \FMUP\ErrorHandler\Plugin\ErrorController($errorController);
        $this->assertInstanceOf(\FMUP\ErrorHandler\Plugin\Abstraction::class, $errorPlugin);
        $this->assertInstanceOf(\FMUP\Controller\Error::class, $errorPlugin->getErrorController());
        $this->assertSame($errorController, $errorPlugin->getErrorController());
        $this->assertSame($errorPlugin, $errorPlugin->setErrorController($errorController2));
        $this->assertSame($errorController2, $errorPlugin->getErrorController());
    }

    public function testGetErrorControllerFailsWhenNoErrorController()
    {
        $plugin = new ErrorControllerMockErrorController;
        /** @var $plugin \FMUP\ErrorHandler\Plugin\ErrorController */
        $this->expectException(\FMUP\Exception::class);
        $this->expectExceptionMessage('Error Controller must be set');
        $plugin->getErrorController();
    }

    public function testCanHandle()
    {
        $errorController = $this->getMockBuilder(\FMUP\Controller\Error::class)->setMethods(array('render'))->getMock();
        /** @var $errorController \FMUP\Controller\Error */
        $errorPlugin = new \FMUP\ErrorHandler\Plugin\ErrorController($errorController);
        $this->assertInstanceOf(\FMUP\ErrorHandler\Plugin\Abstraction::class, $errorPlugin);
        $this->assertTrue($errorPlugin->canHandle());
    }

    public function testHandle()
    {
        $bootstrap = $this->getMockBuilder(\FMUP\Bootstrap::class)->getMock();
        $request = $this->getMockBuilder(\FMUP\Request\Cli::class)->getMock();
        $response = $this->getMockBuilder(\FMUP\Response::class)->getMock();
        $exception = $this->getMockBuilder(\FMUP\Exception::class)->getMock();
        $errorController = $this->getMockBuilder(\FMUP\Controller\Error::class)
            ->setMethods(
                array('render',
                    'preFilter',
                    'postFilter',
                    'indexAction',
                    'setBootstrap',
                    'setRequest',
                    'setResponse',
                    'setException',
                )
            )
            ->getMock();
        $errorController->expects($this->exactly(1))->method('preFilter')->with($this->equalTo('index'));
        $errorController->expects($this->exactly(1))->method('postFilter')->with($this->equalTo('index'));
        $errorController->expects($this->exactly(1))->method('indexAction');
        $errorController->expects($this->exactly(1))->method('setBootstrap')->with($this->equalTo($bootstrap))->willReturn($errorController);
        $errorController->expects($this->exactly(1))->method('setRequest')->with($this->equalTo($request))->willReturn($errorController);
        $errorController->expects($this->exactly(1))->method('setResponse')->with($this->equalTo($response))->willReturn($errorController);
        $errorController->expects($this->exactly(1))->method('setException')->with($this->equalTo($exception))->willReturn($errorController);
        /** @var $errorController \FMUP\Controller\Error */

        $plugin = $this->getMockBuilder(\FMUP\ErrorHandler\Plugin\ErrorController::class)
            ->setMethods(array('getBootstrap', 'getRequest', 'getResponse', 'getException'))
            ->setConstructorArgs(array($errorController))
            ->getMock();
        $plugin->expects($this->exactly(1))->method('getBootstrap')->willReturn($bootstrap);
        $plugin->expects($this->exactly(1))->method('getRequest')->willReturn($request);
        $plugin->expects($this->exactly(1))->method('getResponse')->willReturn($response);
        $plugin->expects($this->exactly(1))->method('getException')->willReturn($exception);

        /** @var $plugin \FMUP\ErrorHandler\Plugin\ErrorController */
        $this->assertInstanceOf(\FMUP\ErrorHandler\Plugin\Abstraction::class, $plugin);
        $this->assertSame($plugin, $plugin->handle());
    }
}
