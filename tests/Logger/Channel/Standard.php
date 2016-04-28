<?php
/**
 * Standard.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Logger\Channel;

class SapiMockChannelStandard extends \FMUP\Sapi
{
    public function __construct()
    {

    }
}

class EnvironmentMockChannelStandard extends \FMUP\Environment
{
    public function __construct()
    {

    }
}

class StandardTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigure()
    {
        $environment = $this->getMock(EnvironmentMockChannelStandard::class, array('get'));
        $environment->method('get')->willReturnOnConsecutiveCalls(EnvironmentMockChannelStandard::DEV, EnvironmentMockChannelStandard::PROD);
        $sapi = $this->getMock(SapiMockChannelStandard::class, array('get'));
        $sapi->method('get')->willReturnOnConsecutiveCalls(SapiMockChannelStandard::CLI, SapiMockChannelStandard::CGI);
        $monologChannel = $this->getMock(\Monolog\Logger::class, array('pushHandler'), array('Mock'));
        $monologChannel->method('pushHandler')->willReturn($monologChannel);
        $channel = $this->getMock(\FMUP\Logger\Channel\Standard::class, array('getLogger', 'headerSent', 'getSapi', 'getEnvironment'));
        $channel->method('getLogger')->willReturn($monologChannel);
        $channel->method('headerSent')->willReturn(false);
        $channel->method('getSapi')->willReturn($sapi);
        $channel->method('getEnvironment')->willReturn($environment);
        /** @var $channel \FMUP\Logger\Channel\Standard */
        $this->assertInstanceOf(\FMUP\Logger\Channel::class, $channel);
        $this->assertInstanceOf(\FMUP\Logger\Channel\System::class, $channel);
        $this->assertSame($channel, $channel->configure());
        $_SERVER['HTTP_USER_AGENT'] = 'Castelis';
        $this->assertSame($channel, $channel->configure());
    }
}
