<?php
/**
 * Framework.php
 * @author: jmoulin@castelis.com
 */

namespace Tests;

use FMUP\Exception\Status\NotFound;
use FMUP\Exception\Location;
use FMUP\Logger;

class SapiMockFramework extends \FMUP\Sapi
{
    public function __construct()
    {

    }
}

if (!class_exists('\Tests\ControllerMock')) {
    class ControllerMock extends \FMUP\Controller
    {
        public function testAction()
        {
            echo 'expected output!!';
        }

        public function testWithReturnAction()
        {
            return 'expected output!!';
        }
    }
}

class FrameworkTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGetBootstrap()
    {
        $bootstrap = $this->getMockBuilder('\FMUP\Bootstrap')->getMock();
        /** @var $bootstrap \FMUP\Bootstrap */
        $framework = new \FMUP\Framework();
        $this->assertInstanceOf('\FMUP\Bootstrap', $framework->getBootstrap());
        $this->assertSame($framework, $framework->setBootstrap($bootstrap));
        $this->assertInstanceOf('\FMUP\Bootstrap', $framework->getBootstrap());
        $this->assertSame($bootstrap, $framework->getBootstrap());
    }

    public function testSetGetPostDispatcherSystem()
    {
        $postDispatcher = $this->getMockBuilder('\FMUP\Dispatcher')->getMock();
        /** @var $postDispatcher \FMUP\Dispatcher */
        $framework = new \FMUP\Framework();
        $this->assertInstanceOf('\FMUP\Dispatcher', $framework->getPostDispatcherSystem());
        $this->assertInstanceOf('\FMUP\Dispatcher\Post', $framework->getPostDispatcherSystem());
        $this->assertSame($framework, $framework->setPostDispatcherSystem($postDispatcher));
        $this->assertInstanceOf('\FMUP\Dispatcher', $framework->getPostDispatcherSystem());
        $this->assertNotInstanceOf('\FMUP\Dispatcher\Post', $framework->getPostDispatcherSystem());
        $this->assertSame($postDispatcher, $framework->getPostDispatcherSystem());
    }

    public function testSetGetPreDispatcherSystem()
    {
        $postDispatcher = $this->getMockBuilder('\FMUP\Dispatcher')->getMock();
        /** @var $postDispatcher \FMUP\Dispatcher */
        $framework = new \FMUP\Framework();
        $this->assertInstanceOf('\FMUP\Dispatcher', $framework->getPreDispatcherSystem());
        $this->assertSame($framework, $framework->setPreDispatcherSystem($postDispatcher));
        $this->assertInstanceOf('\FMUP\Dispatcher', $framework->getPreDispatcherSystem());
        $this->assertSame($postDispatcher, $framework->getPreDispatcherSystem());
    }

    public function testSetGetErrorHandler()
    {
        $errorHandler = $this->getMockBuilder('\FMUP\ErrorHandler')->getMock();
        /** @var $errorHandler \FMUP\ErrorHandler */
        $framework = new \FMUP\Framework();
        $this->assertInstanceOf('\FMUP\ErrorHandler', $framework->getErrorHandler());
        $this->assertInstanceOf('\FMUP\ErrorHandler\Base', $framework->getErrorHandler());
        $this->assertSame($framework, $framework->setErrorHandler($errorHandler));
        $this->assertInstanceOf('\FMUP\ErrorHandler', $framework->getErrorHandler());
        $this->assertSame($errorHandler, $framework->getErrorHandler());
    }

    public function testSetGetResponse()
    {
        $response = $this->getMockBuilder('\FMUP\Response')->getMock();
        /** @var $response \FMUP\Response */
        $framework = new \FMUP\Framework();
        $this->assertInstanceOf('\FMUP\Response', $framework->getResponse());
        $this->assertSame($framework, $framework->setResponse($response));
        $this->assertInstanceOf('\FMUP\Response', $framework->getResponse());
        $this->assertSame($response, $framework->getResponse());
    }

    public function testSetGetRoutingSystem()
    {
        $routing = $this->getMockBuilder('\FMUP\Routing')->getMock();
        /** @var $routing \FMUP\Routing */
        $framework = new \FMUP\Framework();
        $this->assertInstanceOf('\FMUP\Routing', $framework->getRoutingSystem());
        $this->assertSame($framework, $framework->setRoutingSystem($routing));
        $this->assertInstanceOf('\FMUP\Routing', $framework->getRoutingSystem());
        $this->assertSame($routing, $framework->getRoutingSystem());
    }

    public function testGetRequestCli()
    {
        $sapi = $this->getMockBuilder('\Tests\SapiMockFramework')->setMethods(array('getRaw'))->getMock();
        $sapi->method('getRaw')->willReturn(SapiMockFramework::CLI);

        $reflection = new \ReflectionProperty('\FMUP\Sapi', 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($sapi);

        /**
         * @var $sapi \FMUP\Sapi
         */
        $framework = new \FMUP\Framework();
        $this->assertSame($framework, $framework->setSapi($sapi));
        $this->assertInstanceOf('\FMUP\Request', $framework->getRequest());
        $this->assertInstanceOf('\FMUP\Request\Cli', $framework->getRequest());
    }

    public function testGetRequestHttp()
    {
        $sapi = $this->getMockBuilder('\Tests\SapiMockFramework')->setMethods(array('getRaw'))->getMock();
        $requestCli = $this->getMockBuilder('\FMUP\Request\Cli')->getMock();
        $sapi->method('getRaw')->willReturn(SapiMockFramework::CGI);

        $reflection = new \ReflectionProperty('\FMUP\Sapi', 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($sapi);

        /**
         * @var $sapi \FMUP\Sapi
         * @var $requestCli \FMUP\Request\Cli
         */
        $framework = new \FMUP\Framework();
        $this->assertSame($framework, $framework->setSapi($sapi));
        $this->assertInstanceOf('\FMUP\Request', $framework->getRequest());
        $this->assertInstanceOf('\FMUP\Request\Http', $framework->getRequest());
        $this->assertSame($framework, $framework->setRequest($requestCli));
        $this->assertInstanceOf('\FMUP\Request', $framework->getRequest());
        $this->assertInstanceOf('\FMUP\Request\Cli', $framework->getRequest());
        $this->assertSame($requestCli, $framework->getRequest());
    }

    public function testGetRouteError()
    {
        $this->setExpectedException('\FMUP\Exception\Status\NotFound', 'Controller not found directory/controller');
        (new \FMUP\Framework())->getRouteError('directory', 'controller');
    }

    public function testInitializeWithoutConfiguration()
    {
        $framework = $this->getMockBuilder('\FMUP\Framework')
            ->setMethods(array('dispatch', 'registerErrorHandler'))
            ->getMock();
        /** @var $framework \FMUP\Framework */
        $bootstrap = $this->getMockBuilder('\FMUP\Bootstrap')
            ->setMethods(array('setSapi', 'setRequest', 'setConfig', 'warmUp', 'hasSapi', 'hasRequest', 'hasConfig'))
            ->getMock();
        $bootstrap->method('hasSapi')->willReturn(true);
        $bootstrap->expects($this->never())->method('setSapi');
        $bootstrap->method('hasRequest')->willReturn(true);
        $bootstrap->expects($this->never())->method('setRequest');
        $bootstrap->method('hasConfig')->willReturn(true);
        $bootstrap->expects($this->never())->method('setConfig');
        $bootstrap->expects($this->exactly(1))->method('warmUp');
        /** @var $bootstrap \FMUP\Bootstrap */
        $framework->setBootstrap($bootstrap)->initialize();
    }

    public function testInitializeWithinConfiguration()
    {
        $framework = $this->getMockBuilder('\FMUP\Framework')->setMethods(array('dispatch'))->getMock();
        /** @var $framework \FMUP\Framework */
        $bootstrap = $this->getMockBuilder('\FMUP\Bootstrap')
            ->setMethods(array('setSapi', 'setRequest', 'setConfig', 'warmUp', 'hasSapi', 'hasRequest', 'hasConfig'))
            ->getMock();
        $bootstrap->method('hasSapi')->willReturn(false);
        $bootstrap->expects($this->exactly(1))->method('setSapi')->with($framework->getSapi());
        $bootstrap->method('hasRequest')->willReturn(false);
        $bootstrap->expects($this->exactly(1))->method('setRequest')->with($framework->getRequest());
        $bootstrap->method('hasConfig')->willReturn(false);
        $bootstrap->expects($this->exactly(1))->method('setConfig')->with($framework->getConfig());
        $bootstrap->expects($this->exactly(1))->method('warmUp');
        /** @var $bootstrap \FMUP\Bootstrap */
        $framework->setBootstrap($bootstrap)->initialize();
    }

    public function testShutDownWhenNoError()
    {
        $sapi = $this->getMockBuilder('\Tests\SapiMockFramework')->setMethods(array('getRaw'))->getMock();
        $sapi->method('getRaw')->willReturn(SapiMockFramework::CGI);

        $reflection = new \ReflectionProperty('\FMUP\Sapi', 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($sapi);

        $logger = $this->getMockBuilder('\FMUP\Logger')->setMethods(array('log'))->getMock();
        $bootstrap = $this->getMockBuilder('\FMUP\Bootstrap')->setMethods(array('getLogger', 'hasLogger'))->getMock();
        $bootstrap->method('getLogger')->willReturn($logger);
        $bootstrap->method('hasLogger')->willReturn(true);
        $fakeHeader = $this->getMockBuilder('\FMUP\Response\Header')->setMethods(array('getType', 'render'))->getMock();
        $fakeHeader->expects($this->exactly(0))->method('render');
        /** @var \FMUP\Response\Header $fakeHeader */
        $framework = $this->getMockBuilder('\FMUP\Framework')
            ->setMethods(array('isDebug', 'errorGetLast', 'errorHandler', 'getErrorHeader', 'getLogger'))
            ->getMock();
        $framework->method('errorGetLast');
        $framework->expects($this->exactly(0))->method('errorHandler');
        $framework->expects($this->exactly(0))->method('getErrorHeader')->willReturn($fakeHeader);
        /**
         * @var $framework \FMUP\Framework
         * @var $bootstrap \FMUP\Bootstrap
         */
        $framework->setBootstrap($bootstrap)->setSapi($sapi)->shutDown();
    }

    public function testShutDownWhenErrorOccurs()
    {
        $sapi = $this->getMockBuilder('\Tests\SapiMockFramework')->setMethods(array('getRaw'))->getMock();
        $sapi->method('getRaw')->willReturn(SapiMockFramework::CGI);

        $reflection = new \ReflectionProperty('\FMUP\Sapi', 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($sapi);

        $logger = $this->getMockBuilder('\FMUP\Logger')->setMethods(array('log'))->getMock();
        $bootstrap = $this->getMockBuilder('\FMUP\Bootstrap')->setMethods(array('getLogger', 'hasLogger'))->getMock();
        $bootstrap->method('getLogger')->willReturn($logger);
        $bootstrap->method('hasLogger')->willReturn(true);
        $fakeHeader = $this->getMockBuilder('\FMUP\Response\Header')->setMethods(array('getType', 'render'))->getMock();
        $fakeHeader->expects($this->exactly(1))->method('render');
        /** @var \FMUP\Response\Header $fakeHeader */
        $framework = $this->getMockBuilder('\FMUP\Framework')
            ->setMethods(
                array('errorHandler', 'isDebug', 'errorGetLast', 'getErrorHeader', 'getLogger', 'getSapi',
                    'getBootstrap', 'registerErrorHandler'
                )
            )
            ->getMock();
        $framework->method('isDebug')->willReturn(false);
        $framework->expects($this->exactly(1))->method('errorHandler');
        $framework->expects($this->exactly(1))->method('getErrorHeader')->willReturn($fakeHeader);
        $framework->method('errorGetLast')
            ->willReturn(array('type' => E_USER_ERROR, 'message' => 'message', 'file' => __FILE__, 'line' => __LINE__));
        $framework->method('getSapi')->willReturn($sapi);
        $framework->method('getBootstrap')->willReturn($bootstrap);
        /**
         * @var $framework \FMUP\Framework
         * @var $bootstrap \FMUP\Bootstrap
         */
        $this->expectOutputString("<br/>Une erreur est survenue !<br/>"
. "Le support informatique a été prévenu "
. "et règlera le problême dans les plus brefs délais.<br/>"
. "<br/>"
. "L'équipe des développeurs vous prie de l'excuser pour le désagrément.<br/>");
        $framework->shutDown();
    }

    public function testDispatchWhenNoRouteExists()
    {
        $framework = $this->getMockBuilder('\FMUP\Framework')
            ->setMethods(array('registerErrorHandler', 'registerShutdownFunction'))
            ->getMock();
        /** @var $framework \FMUP\Framework */
        $errorHandler = $this->getMockBuilder('\FMUP\ErrorHandler')->setMethods(null)->getMock();
        /** @var $errorHandler \FMUP\ErrorHandler */
        $request = $this->getMockBuilder('\FMUP\Request\Cli')->setMethods(array('get'))->getMock();
        $request->method('get')->willReturn('route/index');
        /** @var $request \FMUP\Request\Cli */
        $framework->setRequest($request);
        $bootstrap = $this->getMockBuilder('\FMUP\Bootstrap')
            ->setMethods(array('setSapi', 'setRequest', 'setConfig', 'warmUp', 'hasSapi', 'hasRequest', 'hasConfig'))
            ->getMock();
        $bootstrap->method('hasSapi')->willReturn(false);
        $bootstrap->expects($this->exactly(1))->method('setSapi')->with($framework->getSapi());
        $bootstrap->method('hasRequest')->willReturn(false);
        $bootstrap->expects($this->exactly(1))->method('setRequest')->with($framework->getRequest());
        $bootstrap->method('hasConfig')->willReturn(false);
        $bootstrap->expects($this->exactly(1))->method('setConfig')->with($framework->getConfig());
        $bootstrap->expects($this->exactly(1))->method('warmUp');
        /** @var $bootstrap \FMUP\Bootstrap */
        $this->setExpectedException('\FMUP\Exception\Status\NotFound');
        $framework->setErrorHandler($errorHandler)->setBootstrap($bootstrap)->initialize();
    }

    public function testDispatchWhenRelocate()
    {
        $framework = $this->getMockBuilder('\FMUP\Framework')
            ->setMethods(array('registerErrorHandler', 'registerShutdownFunction', 'preDispatch'))
            ->getMock();
        $framework->method('preDispatch')->willThrowException(new Location('/route/route'));
        $errorHandler = $this->getMockBuilder('\FMUP\ErrorHandler')->setMethods(null)->getMock();
        $postDispatch = $this->getMockBuilder('\FMUP\Dispatcher')->setMethods(null)->getMock();
        $request = $this->getMockBuilder('\FMUP\Request\Cli')->setMethods(array('get'))->getMock();
        $request->method('get')->willReturn('/route/index');

        /**
         * @var $framework \FMUP\Framework
         * @var $errorHandler \FMUP\ErrorHandler
         * @var $request \FMUP\Request\Cli
         */
        $framework->setRequest($request);
        $bootstrap = $this->getMockBuilder('\FMUP\Bootstrap')->setMethods(
            array('setSapi', 'setRequest', 'setConfig', 'warmUp', 'hasSapi', 'hasRequest', 'hasConfig')
        )
        ->getMock();
        $bootstrap->method('hasSapi')->willReturn(false);
        $bootstrap->expects($this->exactly(1))->method('setSapi')->with($framework->getSapi());
        $bootstrap->method('hasRequest')->willReturn(false);
        $bootstrap->expects($this->exactly(1))->method('setRequest')->with($framework->getRequest());
        $bootstrap->method('hasConfig')->willReturn(false);
        $bootstrap->expects($this->exactly(1))->method('setConfig')->with($framework->getConfig());
        $bootstrap->expects($this->exactly(1))->method('warmUp');

        /** @var $bootstrap \FMUP\Bootstrap */
        /** @var $postDispatch \FMUP\Dispatcher */
        $framework->setErrorHandler($errorHandler)
            ->setPostDispatcherSystem($postDispatch)
            ->setBootstrap($bootstrap)
            ->initialize();
        $this->assertEquals(
            array(
                'Location' => array(
                    new \FMUP\Response\Header\Location('/route/route')
                )
            ),
            $framework->getResponse()->getHeaders()
        );
    }

    public function testGetRouteWhenRouteExists()
    {
        $routeMock = $this->getMockBuilder('\FMUP\Routing\Route')
            ->setMethods(array('getControllerName', 'getAction', 'canHandle'))
            ->getMock();
        $routeMock->method('getControllerName')->willReturn('RouteController');
        $routeMock->method('getAction')->willReturn('RouteAction');
        $routingSystem = $this->getMockBuilder('\FMUP\Routing')->setMethods(array('dispatch'))->getMock();
        $routingSystem->method('dispatch')->willReturn($routeMock);
        /** @var $routingSystem \FMUP\Routing */
        $this->assertEquals(
            array('RouteController', 'RouteAction'),
            (new \FMUP\Framework())->setRoutingSystem($routingSystem)->getRoute()
        );
    }

    public function testErrorHandler()
    {
        $sapi = $this->getMockBuilder('\Tests\SapiMockFramework')->setMethods(array('getRaw'))->getMock();
        $sapi->method('getRaw')->willReturn(SapiMockFramework::CLI);

        $reflection = new \ReflectionProperty('\FMUP\Sapi', 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($sapi);

        $logger = $this->getMockBuilder('\FMUP\Logger')->setMethods(array('log'))->getMock();
        $logger->expects($this->at(0))
            ->method('log')
            ->with(
                $this->equalTo(\FMUP\Logger\Channel\System::NAME),
                $this->equalTo(Logger::NOTICE),
                $this->matchesRegularExpression('~^test 1 in ~'),
                $this->equalTo(array())
            );
        $logger->expects($this->at(1))
            ->method('log')
            ->with(
                $this->equalTo(\FMUP\Logger\Channel\System::NAME),
                $this->equalTo(Logger::ERROR),
                $this->equalTo('test 2 in file on line 12'),
                $this->equalTo(array('test' => 'test'))
            );
        $pluginMailMock = $this->getMockBuilder('\FMUP\ErrorHandler\Plugin\Mail')->setMethods(array('handle'))->getMock();
        $pluginMailMock->expects($this->exactly(1))->method('handle');
        $bootstrap = $this->getMockBuilder('\FMUP\Bootstrap')->setMethods(array('getLogger'))->getMock();
        $bootstrap->method('getLogger')->willReturn($logger);
        $request = $this->getMockBuilder('\FMUP\Request\Cli')->setMethods(null)->getMock();
        $framework = $this->getMockBuilder('\FMUP\Framework')
            ->setMethods(array('getBootstrap', 'getRequest', 'createPluginMail', 'phpExit'))
            ->getMock();
        $framework->method('getBootstrap')->willReturn($bootstrap);
        $framework->method('getRequest')->willReturn($request);
        $framework->method('createPluginMail')->willReturn($pluginMailMock);
        $framework->expects($this->once())->method('phpExit')->with($this->equalTo(E_ERROR));
        /** @var $framework \FMUP\Framework */
        $framework->setSapi($sapi)->errorHandler(E_NOTICE, 'test 1');
        $framework->setSapi($sapi)->errorHandler(E_ERROR, 'test 2', 'file', 12, array('test' => 'test'));
    }

    public function testInstantiateWhenClassDontExist()
    {
        $request = $this->getMockBuilder('\FMUP\Request\Cli')->setMethods(null)->getMock();
        $framework = $this->getMockBuilder('\FMUP\Framework')->setMethods(array('getRequest'))->getMock();
        $framework->method('getRequest')->willReturn($request);
        $this->setExpectedException('\FMUP\Exception\Status\NotFound', 'Controller does not exist');
        $reflection = new \ReflectionMethod('\FMUP\Framework', 'instantiate');
        $reflection->setAccessible(true);
        $reflection->invoke($framework, 'controllerName', 'actionName');
    }

    public function testInstantiateWhenClassExistButAction()
    {
        $request = $this->getMockBuilder('\FMUP\Request\Cli')->setMethods(null)->getMock();
        $framework = $this->getMockBuilder('\FMUP\Framework')->setMethods(array('getRequest'))->getMock();
        $framework->method('getRequest')->willReturn($request);
        $this->setExpectedException('\FMUP\Exception\Status\NotFound', 'Undefined function actionNameAction');
        $reflection = new \ReflectionMethod('\FMUP\Framework', 'instantiate');
        $reflection->setAccessible(true);
        $reflection->invoke($framework, '\Tests\ControllerMock', 'actionName');
    }

    public function testInstantiateWithoutActionReturn()
    {
        $request = $this->getMockBuilder('\FMUP\Request\Cli')->setMethods(null)->getMock();
        $framework = $this->getMockBuilder('\FMUP\Framework')->setMethods(array('getRequest'))->getMock();
        $framework->method('getRequest')->willReturn($request);
        $this->expectOutputString('expected output!!');
        $reflection = new \ReflectionMethod('\FMUP\Framework', 'instantiate');
        $reflection->setAccessible(true);
        $this->assertInstanceOf('\FMUP\Controller', $reflection->invoke($framework, '\Tests\ControllerMock', 'test'));
    }

    public function testInstantiateWithinActionReturn()
    {
        $request = $this->getMockBuilder('\FMUP\Request\Cli')->setMethods(null)->getMock();
        $framework = $this->getMockBuilder('\FMUP\Framework')->setMethods(array('getRequest'))->getMock();
        $framework->method('getRequest')->willReturn($request);
        /** @var $framework \FMUP\Framework */
        $reflection = new \ReflectionMethod('\FMUP\Framework', 'instantiate');
        $reflection->setAccessible(true);
        $this->assertInstanceOf('\FMUP\Controller', $reflection->invoke($framework, '\Tests\ControllerMock', 'testWithReturn'));
        $this->assertSame('expected output!!', $framework->getResponse()->getBody());
    }
}
