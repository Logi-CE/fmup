<?php
/**
 * Native.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Queue\Driver;

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
        $channel = $this->getMockBuilder(\FMUP\Queue\Channel::class)
            ->setMethods(null)
            ->setConstructorArgs(array('17'))
            ->getMock();
        $native = $this->getMockBuilder(\FMUP\Queue\Driver\Native::class)->setMethods(array('msgGetQueue'))->getMock();
        $native->expects($this->once())->method('msgGetQueue')->with($this->equalTo(17))->willReturn('resource');
        /** @var $native \FMUP\Queue\Driver\Native */
        /** @var $channel \FMUP\Queue\Channel */
        $this->assertSame($channel, $native->connect($channel));
        $this->assertSame($channel, $native->connect($channel));
    }

    public function testConnectNonNumericName()
    {
        $environment = $this->getMockBuilder(EnvironmentMockQueueDriverNative::class)->setMethods(array('get'))->getMock();
        $environment->method('get')->willReturn('unitTest');
        $channel = $this->getMockBuilder(\FMUP\Queue\Channel::class)
            ->setMethods(null)
            ->setConstructorArgs(array('hello'))
            ->getMock();
        $native = $this->getMockBuilder(\FMUP\Queue\Driver\Native::class)
            ->setMethods(array('msgGetQueue', 'hasEnvironment', 'getEnvironment'))
            ->getMock();
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
        $channel = $this->getMockBuilder(\FMUP\Queue\Channel::class)
            ->setMethods(null)
            ->setConstructorArgs(array('0'))
            ->getMock();
        $native = $this->getMockBuilder(\FMUP\Queue\Driver\Native::class)
            ->setMethods(array('msgGetQueue'))
            ->getMock();
        /** @var $native \FMUP\Queue\Driver\Native */
        /** @var $channel \FMUP\Queue\Channel */
        $this->expectException(\FMUP\Queue\Exception::class);
        $this->expectExceptionMessage('Queue name must be in INT > 0 to use semaphores');
        $native->connect($channel);
    }

    public function testExists()
    {
        $channel = $this->getMockBuilder(\FMUP\Queue\Channel::class)
            ->setMethods(null)
            ->setConstructorArgs(array('17'))
            ->getMock();
        $native = $this->getMockBuilder(\FMUP\Queue\Driver\Native::class)
            ->setMethods(array('msgQueueExists'))
            ->getMock();
        $native->expects($this->once())->method('msgQueueExists')->with($this->equalTo(17))->willReturn(true);
        /** @var $native \FMUP\Queue\Driver\Native */
        /** @var $channel \FMUP\Queue\Channel */
        $this->assertTrue($native->exists($channel));
    }

    public function testPullFailWhenMessageTypeNotValid()
    {
        $channel = $this->getMockBuilder(\FMUP\Queue\Channel::class)
            ->setMethods(null)
            ->setConstructorArgs(array('17'))
            ->getMock();
        $native = $this->getMockBuilder(\FMUP\Queue\Driver\Native::class)
            ->setMethods(array('msgGetQueue'))
            ->getMock();
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
        $channel = $this->getMockBuilder(\FMUP\Queue\Channel::class)
            ->setMethods(array('getSettings'))
            ->setConstructorArgs(array('17'))
            ->getMock();
        $channel->method('getSettings')->willReturn($settings);
        $native = $this->getMockBuilder(\FMUP\Queue\Driver\Native::class)
            ->setMethods(array('msgGetQueue', 'getConfiguration', 'msgReceive'))
            ->getMock();
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
        $channel = $this->getMockBuilder(\FMUP\Queue\Channel::class)
            ->setMethods(array('getSettings'))
            ->setConstructorArgs(array('17'))
            ->getMock();
        $channel->method('getSettings')->willReturn($settings);
        $native = $this->getMockBuilder(\FMUP\Queue\Driver\Native::class)
            ->setMethods(array('msgGetQueue', 'getConfiguration', 'msgReceive'))
            ->getMock();
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
        $channel = $this->getMockBuilder(\FMUP\Queue\Channel::class)
            ->setMethods(array('getSettings', 'getResource'))
            ->setConstructorArgs(array('17'))
            ->getMock();
        $channel->method('getSettings')->willReturn($settings);
        $channel->method('getResource')->willReturn('resource');
        $native = $this->getMockBuilder(\FMUP\Queue\Driver\Native::class)->setMethods(array('msgSetQueue'))->getMock();
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
        $channel = $this->getMockBuilder(\FMUP\Queue\Channel::class)
            ->setMethods(array('hasResource', 'getResource'))
            ->setConstructorArgs(array('17'))
            ->getMock();
        $channel->method('hasResource')->willReturn(false);
        $channel->method('getResource')->willReturn('resource');
        $native = $this->getMockBuilder(\FMUP\Queue\Driver\Native::class)
            ->setMethods(array('connect', 'getConfiguration'))
            ->getMock();
        $native->expects($this->once())->method('getConfiguration')->willReturn(array('stats' => 1));
        /** @var $native \FMUP\Queue\Driver\Native */
        /** @var $channel \FMUP\Queue\Channel */
        $this->assertSame(array('stats' => 1), $native->getStats($channel));
    }

    public function testAckMessage()
    {
        $channel = $this->getMockBuilder(\FMUP\Queue\Channel::class)
            ->setMethods(array('hasResource'))
            ->setConstructorArgs(array('17'))
            ->getMock();
        $message = new \FMUP\Queue\Message();
        $native = $this->getMockBuilder(\FMUP\Queue\Driver\Native::class)
            ->setMethods(array('connect', 'getConfiguration'))
            ->getMock();
        /** @var $native \FMUP\Queue\Driver\Native */
        /** @var $channel \FMUP\Queue\Channel */
        $this->assertSame($native, $native->ackMessage($channel, $message));
    }

    public function testDestroy()
    {
        $channel = $this->getMockBuilder(\FMUP\Queue\Channel::class)
            ->setMethods(array('hasResource', 'getResource', 'setResource'))
            ->setConstructorArgs(array('17'))
            ->getMock();
        $channel->method('hasResource')->willReturnOnConsecutiveCalls(false, true, true);
        $channel->method('getResource')->willReturn('resource');
        $channel->expects($this->once())->method('setResource');

        $native = $this->getMockBuilder(\FMUP\Queue\Driver\Native::class)
            ->setMethods(array('connect', 'getConfiguration', 'msgRemoveQueue'))
            ->getMock();
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
        $channel = $this->getMockBuilder(\FMUP\Queue\Channel::class)
            ->setMethods(array('hasResource', 'getResource', 'setResource'))
            ->setConstructorArgs(array('17'))
            ->getMock();
        $channel->method('hasResource')->willReturn(false);
        $channel->method('getResource')->willReturn('resource');

        $index = 0;
        $native = $this->getMockBuilder(\FMUP\Queue\Driver\Native::class)->setMethods(array('connect', 'msgSend'))->getMock();
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
        $channel = $this->getMockBuilder(\FMUP\Queue\Channel::class)
            ->setMethods(array('hasResource', 'getResource', 'setResource'))
            ->setConstructorArgs(array('17'))
            ->getMock();
        $channel->method('hasResource')->willReturn(false);
        $channel->method('getResource')->willReturn('resource');

        $index = 0;
        $native = $this->getMockBuilder(\FMUP\Queue\Driver\Native::class)->setMethods(array('connect', 'msgSend'))->getMock();
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
