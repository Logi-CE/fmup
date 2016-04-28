<?php
/**
 * Error.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Logger\Channel;

class SapiMockChannelError extends \FMUP\Sapi
{
    public function __construct()
    {

    }
}

class EnvironmentMockChannelError extends \FMUP\Environment
{
    public function __construct()
    {

    }
}

class ProjectVersionMockChannelError extends \FMUP\ProjectVersion
{
    public function __construct()
    {

    }
}

class ErrorTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigure()
    {
        $projectVersion = $this->getMock(ProjectVersionMockChannelError::class, array('name'));
        $config = $this->getMock(\FMUP\Config::class, array('get'));
        $environment = $this->getMock(EnvironmentMockChannelError::class, array('get'));
        $environment->method('get')->willReturnOnConsecutiveCalls(EnvironmentMockChannelError::DEV, EnvironmentMockChannelError::PROD);
        $sapi = $this->getMock(SapiMockChannelError::class, array('get'));
        $sapi->method('get')->willReturnOnConsecutiveCalls(SapiMockChannelError::CLI, SapiMockChannelError::CGI);
        $monologChannel = $this->getMock(\Monolog\Logger::class, array('pushHandler', 'pushProcessor'), array('Mock'));
        $monologChannel->method('pushHandler')->willReturn($monologChannel);
        $monologChannel->method('pushProcessor')->willReturn($monologChannel);
        $channel = $this->getMock(
            \FMUP\Logger\Channel\Error::class,
            array('getLogger', 'headerSent', 'getSapi', 'getEnvironment', 'getConfig', 'getProjectVersion')
        );
        $channel->method('getProjectVersion')->willReturn($projectVersion);
        $channel->method('getLogger')->willReturn($monologChannel);
        $channel->method('headerSent')->willReturn(false);
        $channel->method('getSapi')->willReturn($sapi);
        $channel->method('getConfig')->willReturn($config);
        $channel->method('getEnvironment')->willReturn($environment);
        /** @var $channel \FMUP\Logger\Channel\Error */
        $this->assertInstanceOf(\FMUP\Logger\Channel::class, $channel);
        $this->assertInstanceOf(\FMUP\Logger\Channel\Standard::class, $channel);
        $this->assertSame($channel, $channel->configure());
        $_SERVER['HTTP_USER_AGENT'] = 'Castelis';
        $this->assertSame($channel, $channel->configure());
    }

    public function testGetProjectVersion()
    {
        $projectVersionMock = $this->getMock(ProjectVersionMockChannelError::class, array('name'));
        $channel = $this->getMock(\FMUP\Logger\Channel\Error::class, null);
        /** @var $channel \FMUP\Logger\Channel\Error */
        /** @var $projectVersionMock ProjectVersionMockChannelError */
        $projectVersion = $channel->getProjectVersion();
        $this->assertInstanceOf(\FMUP\ProjectVersion::class, $projectVersion);
        $this->assertSame($channel, $channel->setProjectVersion($projectVersionMock));
        $this->assertNotSame($projectVersion, $channel->getProjectVersion());
        $this->assertSame($projectVersionMock, $channel->getProjectVersion());
    }
}
