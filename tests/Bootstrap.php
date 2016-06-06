<?php
/**
 * Bootstrap.php
 * @author: jmoulin@castelis.com
 */

namespace Tests;

class SessionMockBootstrap extends \FMUP\Session
{
    public function __construct()
    {

    }
}

class FlashMessengerMockBootstrap extends \FMUP\FlashMessenger
{
    public function __construct()
    {

    }
}

class CookieMockBootstrap extends \FMUP\Cookie
{
    public function __construct()
    {

    }
}

class EnvironmentMockBootstrap extends \FMUP\Environment
{
    public function __construct()
    {

    }
}

class BootstrapTest extends \PHPUnit_Framework_TestCase
{
    public function testWarmUp()
    {
        $logger = $this->getMockBuilder(\FMUP\Logger::class)->setMethods(null)->getMock();
        $config = $this->getMockBuilder(\FMUP\Config::class)->setMethods(null)->getMock();
        $request = $this->getMockBuilder(\FMUP\Request\Cli::class)->setMethods(null)->getMock();
        $bootstrap = $this->getMockBuilder(\FMUP\Bootstrap::class)
            ->setMethods(
                array('defineTimezone', 'getLogger', 'initHelperDb', 'getSection', 'getConfig', 'getRequest', 'hasRequest')
            )
            ->getMock();
        $bootstrap->expects($this->exactly(1))->method('defineTimezone');
        $bootstrap->method('getLogger')->willReturn($logger);
        $bootstrap->method('hasRequest')->willReturn(true);
        $bootstrap->method('getRequest')->willReturn($request);
        $bootstrap->method('getConfig')->willReturn($config);
        /** @var $bootstrap \FMUP\Bootstrap */
        $this->assertFalse($bootstrap->isWarmed());
        $this->assertSame($bootstrap, $bootstrap->warmUp());
        $this->assertTrue($bootstrap->isWarmed());
        $this->assertSame($bootstrap, $bootstrap->warmUp());
        $bootstrap2 = $this->getMockBuilder(\FMUP\Bootstrap::class)
            ->setMethods(
                array('hasRequest', 'getRequest', 'getConfig')
            )
            ->getMock();
        $bootstrap2->method('hasRequest')->willReturn(true);
        $bootstrap2->method('getRequest')->willReturn($request);
        $bootstrap2->method('getConfig')->willReturn($config);
        /** @var $bootstrap2 \FMUP\Bootstrap */
        $bootstrap2->warmUp();
    }

    public function testSetGetSession()
    {
        $session = new SessionMockBootstrap();
        $bootstrap = new \FMUP\Bootstrap();
        $this->assertInstanceOf(\FMUP\Session::class, $bootstrap->getSession());
        $this->assertSame($bootstrap, $bootstrap->setSession($session));
        $this->assertInstanceOf(\FMUP\Session::class, $bootstrap->getSession());
        $this->assertSame($session, $bootstrap->getSession());
    }

    public function testSetGetLogger()
    {
        $config = $this->getMockBuilder(\FMUP\Config::class)->setMethods(null)->getMock();
        $request = $this->getMockBuilder(\FMUP\Request\Cli::class)->setMethods(null)->getMock();
        $bootstrap = $this->getMockBuilder(\FMUP\Bootstrap::class)
            ->setMethods(array('hasRequest', 'getRequest', 'getConfig'))
            ->getMock();
        $bootstrap->method('hasRequest')->willReturn(true);
        $bootstrap->method('getRequest')->willReturn($request);
        $bootstrap->method('getConfig')->willReturn($config);
        /** @var $bootstrap \FMUP\Bootstrap */
        $this->assertInstanceOf(\FMUP\Logger::class, $bootstrap->getLogger());
        $logger = $this->getMockBuilder(\FMUP\Logger::class)
            ->setMethods(array('setEnvironment'))
            ->getMock();
        $logger->expects($this->exactly(1))->method('setEnvironment')->with($this->equalTo($bootstrap->getEnvironment()));
        /** @var $logger \FMUP\Logger */
        $this->assertSame($bootstrap, $bootstrap->setLogger($logger));
    }

    public function testRegisterErrorHandler()
    {
        $monolog = $this->getMockBuilder(\Monolog\Logger::class)->setMethods(null)->setConstructorArgs(array('name'))->getMock();
        $loggerChannel = $this->getMockBuilder(\FMUP\Logger\Channel::class)
            ->setMethods(array('getLogger', 'getName', 'configure'))
            ->getMock();
        $loggerChannel->expects($this->exactly(1))->method('getLogger')->willReturn($monolog);
        $logger = $this->getMockBuilder(\FMUP\Logger::class)->setMethods(array('get'))->getMock();
        $logger->expects($this->exactly(1))->method('get')->willReturn($loggerChannel);
        $bootstrap = $this->getMockBuilder(\FMUP\Bootstrap::class)->setMethods(array('getLogger'))->getMock();
        $bootstrap->method('getLogger')->willReturn($logger);
        /** @var $bootstrap \FMUP\Bootstrap */
        $this->assertSame($bootstrap, $bootstrap->registerErrorHandler());
        $this->assertSame($bootstrap, $bootstrap->registerErrorHandler());
    }

    public function testGetRequestWhenNoRequest()
    {
        $bootstrap = new \FMUP\Bootstrap();
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Request is not defined');
        $bootstrap->getRequest();
    }

    public function testSetGetHasRequest()
    {
        $request = $this->getMockBuilder(\FMUP\Request\Cli::class)->getMock();
        /** @var $request \FMUP\Request\Cli */
        $bootstrap = new \FMUP\Bootstrap();
        $this->assertFalse($bootstrap->hasRequest());
        $this->assertSame($bootstrap, $bootstrap->setRequest($request));
        $this->assertTrue($bootstrap->hasRequest());
        $this->assertSame($request, $bootstrap->getRequest());
    }

    public function testSetGetFlashMessenger()
    {
        $bootstrap = new \FMUP\Bootstrap();
        $flashMessengerInstance = $bootstrap->getFlashMessenger();
        $this->assertInstanceOf(\FMUP\FlashMessenger::class, $flashMessengerInstance);
        $this->assertSame($flashMessengerInstance, $bootstrap->getFlashMessenger());
        $flashMock = new FlashMessengerMockBootstrap();
        $this->assertSame($bootstrap, $bootstrap->setFlashMessenger($flashMock));
        $this->assertSame($flashMock, $bootstrap->getFlashMessenger());
    }

    public function testSetGetCookie()
    {
        $bootstrap = new \FMUP\Bootstrap();
        $cookieInstance = $bootstrap->getCookie();
        $this->assertInstanceOf(\FMUP\Cookie::class, $cookieInstance);
        $this->assertSame($cookieInstance, $bootstrap->getCookie());
        $cookieMock = new CookieMockBootstrap();
        $this->assertSame($bootstrap, $bootstrap->setCookie($cookieMock));
        $this->assertSame($cookieMock, $bootstrap->getCookie());
    }

    public function testSetEnvironment()
    {
        $config = $this->getMockBuilder(\FMUP\Config::class)->setMethods(null)->getMock();
        $bootstrap = $this->getMockBuilder(\FMUP\Bootstrap::class)->setMethods(array('getConfig'))->getMock();
        $bootstrap->method('getConfig')->willReturn($config);
        $environment = $this->getMockBuilder(EnvironmentMockBootstrap::class)->setMethods(array('hasConfig', 'setConfig'))->getMock();
        $environment->expects($this->exactly(1))->method('hasConfig')->willReturn(false);
        $environment->expects($this->exactly(1))->method('setConfig')->with($this->equalTo($config));
        /** @var $bootstrap \FMUP\Bootstrap */
        $this->assertSame($bootstrap, $bootstrap->setEnvironment($environment));
    }
}
