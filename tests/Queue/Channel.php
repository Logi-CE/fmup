<?php
/**
 * Channel.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Queue;


class ChannelTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGetSettings()
    {
        $channel = new \FMUP\Queue\Channel('test');
        $this->assertSame('test', $channel->getName());
        $this->assertSame($channel, $channel->setName('releaseTest'));
        $this->assertSame('releaseTest', $channel->getName());
        $this->assertInstanceOf(\FMUP\Queue\Channel\Settings::class, $channel->getSettings());
        $settings = new \FMUP\Queue\Channel\Settings();
        $settings->setAutoAck(true);
        $this->assertEquals($channel, $channel->setSettings($settings));
        $this->assertSame($settings, $channel->getSettings());
    }

    public function testGetResourceFail()
    {
        $channel = new \FMUP\Queue\Channel('test');
        $this->expectException(\FMUP\Queue\Exception::class);
        $this->expectExceptionMessage('Resource must be set before using it');
        $channel->getResource();
    }

    public function testSetGetHasResource()
    {
        $channel = new \FMUP\Queue\Channel('test');
        $this->assertFalse($channel->hasResource());
        $this->assertSame($channel, $channel->setResource('test'));
        $this->assertTrue($channel->hasResource());
        $this->assertSame('test', $channel->getResource());
    }
}
