<?php
/**
 * System.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Logger\Channel;


class SystemTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigure()
    {
        $monologChannel = $this->getMockBuilder('\Monolog\Logger')
            ->setMethods(array('pushHandler'))
            ->setConstructorArgs(array('Mock'))
            ->getMock();
        $channel = $this->getMockBuilder('\FMUP\Logger\Channel\System')->setMethods(array('getLogger'))->getMock();
        $channel->method('getLogger')->willReturn($monologChannel);
        /** @var $channel \FMUP\Logger\Channel\System */
        $this->assertInstanceOf('\FMUP\Logger\Channel', $channel);
        $this->assertInstanceOf('\FMUP\Logger\Channel\Syslog', $channel);
        $this->assertSame($channel, $channel->configure());
    }
}
