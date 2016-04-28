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
        $logger = $this->getMock(\FMUP\Logger::class, null);
        $config = $this->getMock(\FMUP\Config::class, null);
        $request = $this->getMock(\FMUP\Request\Cli::class, null);
        $bootstrap = $this->getMock(
            \FMUP\Bootstrap::class,
            array('defineTimezone', 'getLogger', 'initHelperDb', 'getSection', 'getConfig', 'getRequest', 'hasRequest')
        );
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
        $bootstrap2 = $this->getMock(
            \FMUP\Bootstrap::class,
            array('hasRequest', 'getRequest', 'getConfig')
        );
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
        $config = $this->getMock(\FMUP\Config::class, null);
        $request = $this->getMock(\FMUP\Request\Cli::class, null);
        $bootstrap = $this->getMock(
            \FMUP\Bootstrap::class,
            array('hasRequest', 'getRequest', 'getConfig')
        );
        $bootstrap->method('hasRequest')->willReturn(true);
        $bootstrap->method('getRequest')->willReturn($request);
        $bootstrap->method('getConfig')->willReturn($config);
        /** @var $bootstrap \FMUP\Bootstrap */
        $this->assertInstanceOf(\FMUP\Logger::class, $bootstrap->getLogger());
        $logger = $this->getMock(\FMUP\Logger::class, array('setEnvironment'));
        $logger->expects($this->exactly(1))->method('setEnvironment')->with($this->equalTo($bootstrap->getEnvironment()));
        /** @var $logger \FMUP\Logger */
        $this->assertSame($bootstrap, $bootstrap->setLogger($logger));
    }

    public function testRegisterErrorHandler()
    {
        $monolog = $this->getMock(\Monolog\Logger::class, null, array('name'));
        $loggerChannel = $this->getMock(\FMUP\Logger\Channel::class, array('getLogger', 'getName', 'configure'));
        $loggerChannel->expects($this->exactly(1))->method('getLogger')->willReturn($monolog);
        $logger = $this->getMock(\FMUP\Logger::class, array('get'));
        $logger->expects($this->exactly(1))->method('get')->willReturn($loggerChannel);
        $bootstrap = $this->getMock(\FMUP\Bootstrap::class, array('getLogger'));
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
        $request = $this->getMock(\FMUP\Request\Cli::class);
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
        $config = $this->getMock(\FMUP\Config::class, null);
        $bootstrap = $this->getMock(\FMUP\Bootstrap::class, array('getConfig'));
        $bootstrap->method('getConfig')->willReturn($config);
        $environment = $this->getMock(EnvironmentMockBootstrap::class, array('hasConfig', 'setConfig'));
        $environment->expects($this->exactly(1))->method('hasConfig')->willReturn(false);
        $environment->expects($this->exactly(1))->method('setConfig')->with($this->equalTo($config));
        /** @var $bootstrap \FMUP\Bootstrap */
        $this->assertSame($bootstrap, $bootstrap->setEnvironment($environment));
    }
}
