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
        $monologChannel = $this->getMock(\Monolog\Logger::class, array('pushHandler'), array('Mock'));
        $channel = $this->getMock(\FMUP\Logger\Channel\System::class, array('getLogger'));
        $channel->method('getLogger')->willReturn($monologChannel);
        /** @var $channel \FMUP\Logger\Channel\System */
        $this->assertInstanceOf(\FMUP\Logger\Channel::class, $channel);
        $this->assertInstanceOf(\FMUP\Logger\Channel\Syslog::class, $channel);
        $this->assertSame($channel, $channel->configure());
    }
}
