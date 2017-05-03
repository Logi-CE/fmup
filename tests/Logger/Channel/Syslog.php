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
        $monologChannel = $this->getMockBuilder('\Monolog\Logger')
            ->setMethods(array('pushHandler'))
            ->setConstructorArgs(array('Mock'))
            ->getMock();
        $channel = $this->getMockBuilder('\FMUP\Logger\Channel\Syslog')
            ->setMethods(array('getLogger'))
            ->getMock();
        $channel->method('getLogger')->willReturn($monologChannel);
        /** @var $channel \FMUP\Logger\Channel\Syslog */
        $this->assertInstanceOf('\FMUP\Logger\Channel', $channel);
        $this->assertSame($channel, $channel->configure());
    }

    public function testSetGetName()
    {
        $monologChannel = $this->getMockBuilder('\Monolog\Logger')
            ->setMethods(array('pushHandler'))
            ->setConstructorArgs(array('Mock'))
            ->getMock();
        $channel = $this->getMockBuilder('\FMUP\Logger\Channel\Syslog')
            ->setMethods(array('getLogger'))
            ->getMock();
        $channel->method('getLogger')->willReturn($monologChannel);
        /** @var $channel \FMUP\Logger\Channel\Syslog */
        $this->assertInstanceOf('\FMUP\Logger\Channel', $channel);
        $this->assertSame($channel, $channel->setIdentifier('bubu'));
        $this->assertSame('bubu', $channel->getIdentifier());
    }
}
