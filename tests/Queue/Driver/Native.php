<?php
/**
 * Native.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Queue\Driver;

class EnvironmentMockQueueDriverNative extends \FMUP\Environment
{
    public function __construct()
    {

    }
}

class NativeTest extends \PHPUnit_Framework_TestCase
{
    public function testConnect()
    {
        $channel = $this->getMock(\FMUP\Queue\Channel::class, null, array('17'));
        $native = $this->getMock(\FMUP\Queue\Driver\Native::class, array('msgGetQueue'));
        $native->expects($this->once())->method('msgGetQueue')->with($this->equalTo(17))->willReturn('resource');
        /** @var $native \FMUP\Queue\Driver\Native */
        /** @var $channel \FMUP\Queue\Channel */
        $this->assertSame($channel, $native->connect($channel));
        $this->assertSame($channel, $native->connect($channel));
    }

    public function testConnectNonNumericName()
    {
        $environment = $this->getMock(EnvironmentMockQueueDriverNative::class, array('get'));
        $environment->method('get')->willReturn('unitTest');
        $channel = $this->getMock(\FMUP\Queue\Channel::class, null, array('hello'));
        $native = $this->getMock(\FMUP\Queue\Driver\Native::class, array('msgGetQueue', 'hasEnvironment', 'getEnvironment'));
        $native->expects($this->once())->method('msgGetQueue')->with($this->equalTo(1301396))->willReturn('resource');
        $native->method('getEnvironment')->willReturn($environment);
        $native->method('hasEnvironment')->willReturn(true);
        /** @var $native \FMUP\Queue\Driver\Native */
        /** @var $channel \FMUP\Queue\Channel */
        $this->assertSame($channel, $native->connect($channel));
        $this->assertSame($channel, $native->connect($channel));
    }

    public function testConnectFailsDueToChannelNameZero()
    {
        $channel = $this->getMock(\FMUP\Queue\Channel::class, null, array('0'));
        $native = $this->getMock(\FMUP\Queue\Driver\Native::class, array('msgGetQueue'));
        /** @var $native \FMUP\Queue\Driver\Native */
        /** @var $channel \FMUP\Queue\Channel */
        $this->expectException(\FMUP\Queue\Exception::class);
        $this->expectExceptionMessage('Queue name must be in INT > 0 to use semaphores');
        $native->connect($channel);
    }

    public function testExists()
    {
        $channel = $this->getMock(\FMUP\Queue\Channel::class, null, array('17'));
        $native = $this->getMock(\FMUP\Queue\Driver\Native::class, array('msgQueueExists'));
        $native->expects($this->once())->method('msgQueueExists')->with($this->equalTo(17))->willReturn(true);
        /** @var $native \FMUP\Queue\Driver\Native */
        /** @var $channel \FMUP\Queue\Channel */
        $this->assertTrue($native->exists($channel));
    }

    public function testPullFailWhenMessageTypeNotValid()
    {
        $channel = $this->getMock(\FMUP\Queue\Channel::class, null, array('17'));
        $native = $this->getMock(\FMUP\Queue\Driver\Native::class, array('msgGetQueue'));
        $native->expects($this->once())->method('msgGetQueue')->with($this->equalTo(17))->willReturn('resource');
        /** @var $native \FMUP\Queue\Driver\Native */
        /** @var $channel \FMUP\Queue\Channel */
        $this->expectException(\FMUP\Queue\Exception::class);
        $this->expectExceptionMessage('Message Type must be in INT > 0 to use semaphores');
        $native->pull($channel, 0);
    }

    public function testPullFailWhenNotSuccessBlocking()
    {
        $settings = new \FMUP\Queue\Channel\Settings\Native();
        $settings->setMaxMessageSize(0);
        $settings->setBlockReceive(true);
        $channel = $this->getMock(\FMUP\Queue\Channel::class, array('getSettings'), array('17'));
        $channel->method('getSettings')->willReturn($settings);
        $native = $this->getMock(\FMUP\Queue\Driver\Native::class, array('msgGetQueue', 'getConfiguration', 'msgReceive'));
        $native->expects($this->once())->method('msgGetQueue')->with($this->equalTo(17))->willReturn('resource');
        $native->expects($this->once())->method('msgReceive')->willReturn(false);
        $native->method('getConfiguration')->willReturn(array(
            \FMUP\Queue\Driver\Native::CONFIGURATION_MESSAGE_SIZE => 1,
        ));
        /** @var $native \FMUP\Queue\Driver\Native */
        /** @var $channel \FMUP\Queue\Channel */
        $this->expectException(\FMUP\Queue\Exception::class);
        $this->expectExceptionMessage('Error while receiving message');
        $native->pull($channel);
    }

    public function testPull()
    {
        $settings = new \FMUP\Queue\Channel\Settings\Native();
        $settings->setMaxMessageSize(0);
        $settings->setBlockReceive(false);
        $channel = $this->getMock(\FMUP\Queue\Channel::class, array('getSettings'), array('17'));
        $channel->method('getSettings')->willReturn($settings);
        $native = $this->getMock(\FMUP\Queue\Driver\Native::class, array('msgGetQueue', 'getConfiguration', 'msgReceive'));
        $native->expects($this->once())->method('msgGetQueue')->with($this->equalTo(17))->willReturn('resource');
        $native->expects($this->once())->method('msgReceive')->will(
            $this->returnCallback(function ($resource, $messageType, $receivedMessageType, $messageSize, &$message) {
                $message = 'hello';
                return true;
            })
        );
        $native->method('getConfiguration')->willReturn(array(
            \FMUP\Queue\Driver\Native::CONFIGURATION_MESSAGE_SIZE => 1,
        ));
        /** @var $native \FMUP\Queue\Driver\Native */
        /** @var $channel \FMUP\Queue\Channel */
        $message = $native->pull($channel);
        $this->assertInstanceOf(\FMUP\Queue\Message::class, $message);
        $this->assertEquals((new \FMUP\Queue\Message)->setOriginal('hello')->setTranslated('hello'), $message);
        $this->assertEquals(1, $settings->getMaxMessageSize());
    }

    public function testSetConfiguration()
    {
        $settings = new \FMUP\Queue\Channel\Settings\Native();
        $channel = $this->getMock(\FMUP\Queue\Channel::class, array('getSettings', 'getResource'), array('17'));
        $channel->method('getSettings')->willReturn($settings);
        $channel->method('getResource')->willReturn('resource');
        $native = $this->getMock(\FMUP\Queue\Driver\Native::class, array('msgSetQueue'));
        $native->expects($this->once())->method('msgSetQueue')->willReturn(true)
            ->with(
                $this->equalTo('resource'),
                $this->equalTo(array(\FMUP\Queue\Driver\Native::CONFIGURATION_MESSAGE_SIZE => 2))
            );
        /** @var $native \FMUP\Queue\Driver\Native */
        /** @var $channel \FMUP\Queue\Channel */
        $this->assertTrue($native->setConfiguration($channel, array(\FMUP\Queue\Driver\Native::CONFIGURATION_MESSAGE_SIZE => 2)));
    }

    public function testGetStats()
    {
        $channel = $this->getMock(\FMUP\Queue\Channel::class, array('hasResource', 'getResource'), array('17'));
        $channel->method('hasResource')->willReturn(false);
        $channel->method('getResource')->willReturn('resource');
        $native = $this->getMock(\FMUP\Queue\Driver\Native::class, array('connect', 'getConfiguration'));
        $native->expects($this->once())->method('getConfiguration')->willReturn(array('stats' => 1));
        /** @var $native \FMUP\Queue\Driver\Native */
        /** @var $channel \FMUP\Queue\Channel */
        $this->assertSame(array('stats' => 1), $native->getStats($channel));
    }

    public function testAckMessage()
    {
        $channel = $this->getMock(\FMUP\Queue\Channel::class, array('hasResource'), array('17'));
        $message = new \FMUP\Queue\Message();
        $native = $this->getMock(\FMUP\Queue\Driver\Native::class, array('connect', 'getConfiguration'));
        /** @var $native \FMUP\Queue\Driver\Native */
        /** @var $channel \FMUP\Queue\Channel */
        $this->assertSame($native, $native->ackMessage($channel, $message));
    }

    public function testDestroy()
    {
        $channel = $this->getMock(\FMUP\Queue\Channel::class, array('hasResource', 'getResource', 'setResource'), array('17'));
        $channel->method('hasResource')->willReturnOnConsecutiveCalls(false, true, true);
        $channel->method('getResource')->willReturn('resource');
        $channel->expects($this->once())->method('setResource');

        $native = $this->getMock(\FMUP\Queue\Driver\Native::class, array('connect', 'getConfiguration', 'msgRemoveQueue'));
        $native->expects($this->exactly(2))->method('msgRemoveQueue')->willReturnOnConsecutiveCalls(false, true);
        /** @var $native \FMUP\Queue\Driver\Native */
        /** @var $channel \FMUP\Queue\Channel */
        $this->assertTrue($native->destroy($channel));
        $this->assertFalse($native->destroy($channel));
        $this->assertTrue($native->destroy($channel));
    }

    public function testPush()
    {
        $settings = new \FMUP\Queue\Channel\Settings\Native();
        $settings->setMaxSendRetryTime(5);
        $settings->setSerialize(true);
        $settings->setBlockSend(false);
        $channel = $this->getMock(\FMUP\Queue\Channel::class, array('hasResource', 'getResource', 'setResource'), array('17'));
        $channel->method('hasResource')->willReturn(false);
        $channel->method('getResource')->willReturn('resource');

        $index = 0;
        $native = $this->getMock(\FMUP\Queue\Driver\Native::class, array('connect', 'msgSend'));
        $native->expects($this->exactly(3))->method('msgSend')->with(
            $this->equalTo('resource'),
            $this->equalTo(1),
            $this->equalTo('test message'),
            $this->equalTo(true), //serialize
            $this->equalTo(false) // block send
        )
        ->will($this->returnCallback(function ($resource, $messageType, $message, $serialize, $blockSend, &$error) use (&$index) {
            $error = MSG_EAGAIN;
            $return = array(false, false, true);
            return $return[$index++];
        }));
        /** @var $native \FMUP\Queue\Driver\Native */
        /** @var $channel \FMUP\Queue\Channel */
        $this->assertTrue($native->push($channel->setSettings($settings), 'test message'));
    }

    public function testPushFailsOnSendRetryTime()
    {
        $settings = new \FMUP\Queue\Channel\Settings\Native();
        $settings->setMaxSendRetryTime(5);
        $settings->setSerialize(true);
        $settings->setBlockSend(false);
        $channel = $this->getMock(\FMUP\Queue\Channel::class, array('hasResource', 'getResource', 'setResource'), array('17'));
        $channel->method('hasResource')->willReturn(false);
        $channel->method('getResource')->willReturn('resource');

        $index = 0;
        $native = $this->getMock(\FMUP\Queue\Driver\Native::class, array('connect', 'msgSend'));
        $native->expects($this->exactly(5))->method('msgSend')->with(
            $this->equalTo('resource'),
            $this->equalTo(1),
            $this->equalTo('test message'),
            $this->equalTo(true), //serialize
            $this->equalTo(false) // block send
        )
            ->will($this->returnCallback(function ($resource, $messageType, $message, $serialize, $blockSend, &$error) use (&$index) {
                $error = MSG_EAGAIN;
                $return = array(false, false, false, false, false, false, true);
                return $return[$index++];
            }));
        /** @var $native \FMUP\Queue\Driver\Native */
        /** @var $channel \FMUP\Queue\Channel */
        $this->expectException(\FMUP\Queue\Exception::class);
        $this->expectExceptionMessage('Error while sending message');
        $this->assertTrue($native->push($channel->setSettings($settings), 'test message'));
    }
}
