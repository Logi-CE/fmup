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
        $monologChannel = $this->getMockBuilder(\Monolog\Logger::class)
            ->setMethods(array('pushHandler'))
            ->setConstructorArgs(array('Mock'))
            ->getMock();
        $channel = $this->getMockBuilder(\FMUP\Logger\Channel\Syslog::class)
            ->setMethods(array('getLogger'))
            ->getMock();
        $channel->method('getLogger')->willReturn($monologChannel);
        /** @var $channel \FMUP\Logger\Channel\Syslog */
        $this->assertInstanceOf(\FMUP\Logger\Channel::class, $channel);
        $this->assertSame($channel, $channel->configure());
    }
}
