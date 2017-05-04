<?php
/**
 * Error.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Logger\Channel;

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
        $projectVersion = $this->getMockBuilder(ProjectVersionMockChannelError::class)->setMethods(array('name'))->getMock();
        $config = $this->getMockBuilder(\FMUP\Config::class)->setMethods(array('get'))->getMock();
        $environment = $this->getMockBuilder(EnvironmentMockChannelError::class)->setMethods(array('get'))->getMock();
        $environment->method('get')->willReturnOnConsecutiveCalls(EnvironmentMockChannelError::DEV, EnvironmentMockChannelError::PROD);
        $sapi = $this->getMockBuilder(SapiMockChannelError::class)->setMethods(array('get'))->getMock();
        $sapi->method('get')->willReturnOnConsecutiveCalls(SapiMockChannelError::CLI, SapiMockChannelError::CGI);
        $monologChannel = $this->getMockBuilder(\Monolog\Logger::class)
            ->setMethods(array('pushHandler', 'pushProcessor'))
            ->setConstructorArgs(array('Mock'))
            ->getMock();
        $monologChannel->method('pushHandler')->willReturn($monologChannel);
        $monologChannel->method('pushProcessor')->willReturn($monologChannel);
        $channel = $this->getMockBuilder(\FMUP\Logger\Channel\Error::class)
            ->setMethods(
                array('getLogger', 'headerSent', 'getSapi', 'getEnvironment', 'getConfig', 'getProjectVersion')
            )
            ->getMock();
        $channel->method('getProjectVersion')->willReturn($projectVersion);
        $channel->method('getLogger')->willReturn($monologChannel);
        $channel->method('headerSent')->willReturn(false);
        $channel->method('getSapi')->willReturn($sapi);
        $channel->method('getConfig')->willReturn($config);
        $channel->method('getEnvironment')->willReturn($environment);
        /** @var $channel \FMUP\Logger\Channel\Error */
        $this->assertInstanceOf(\FMUP\Logger\Channel::class, $channel);
        $this->assertSame($channel, $channel->configure());
        $_SERVER['HTTP_USER_AGENT'] = 'Castelis';
        $this->assertSame($channel, $channel->configure());
    }

    public function testGetProjectVersion()
    {
        $projectVersionMock = $this->getMockBuilder(ProjectVersionMockChannelError::class)->setMethods(array('name'))->getMock();
        $channel = $this->getMockBuilder(\FMUP\Logger\Channel\Error::class)->setMethods(null)->getMock();
        /** @var $channel \FMUP\Logger\Channel\Error */
        /** @var $projectVersionMock ProjectVersionMockChannelError */
        $projectVersion = $channel->getProjectVersion();
        $this->assertInstanceOf(\FMUP\ProjectVersion::class, $projectVersion);
        $this->assertSame($channel, $channel->setProjectVersion($projectVersionMock));
        $this->assertNotSame($projectVersion, $channel->getProjectVersion());
        $this->assertSame($projectVersionMock, $channel->getProjectVersion());
    }
}
