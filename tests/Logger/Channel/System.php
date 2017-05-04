<?php
/**
 * System.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Logger\Channel;


class SystemTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigure()
    {
        $monologChannel = $this->getMockBuilder(\Monolog\Logger::class)
            ->setMethods(array('pushHandler'))
            ->setConstructorArgs(array('Mock'))
            ->getMock();
        $channel = $this->getMockBuilder(\FMUP\Logger\Channel\System::class)->setMethods(array('getLogger'))->getMock();
        $channel->method('getLogger')->willReturn($monologChannel);
        /** @var $channel \FMUP\Logger\Channel\System */
        $this->assertInstanceOf(\FMUP\Logger\Channel::class, $channel);
        $this->assertSame($channel, $channel->configure());
    }
}
