<?php
/**
 * Syslog.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Logger\Channel;


class SyslogTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigure()
    {
        $monologChannel = $this->getMock(\Monolog\Logger::class, array('pushHandler'), array('Mock'));
        $channel = $this->getMock(\FMUP\Logger\Channel\Syslog::class, array('getLogger'));
        $channel->method('getLogger')->willReturn($monologChannel);
        /** @var $channel \FMUP\Logger\Channel\Syslog */
        $this->assertInstanceOf(\FMUP\Logger\Channel::class, $channel);
        $this->assertSame($channel, $channel->configure());
    }
}
