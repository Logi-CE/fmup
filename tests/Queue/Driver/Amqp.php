<?php
/**
 * Amqp.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Queue\Driver;

use PhpAmqpLib\Message\AMQPMessage;

class AMQPChannelMockQueueDriverAmqp extends \PhpAmqpLib\Channel\AMQPChannel
{
    public function __construct()
    {

    }
}

class AMQPStreamConnectionMockQueueDriverAmqp extends \PhpAmqpLib\Connection\AMQPStreamConnection
{
    public function __construct()
    {

    }
}

class EnvironmentMockQueueDriverAmqp extends \FMUP\Environment
{
    public function __construct()
    {

    }
}

class AmqpTest extends \PHPUnit_Framework_TestCase
{
    public function testConnect()
    {
        $channelResource = $this->getMockBuilder('\Tests\Queue\Driver\AMQPChannelMockQueueDriverAmqp')
            ->setMethods(array('queue_declare'))
            ->getMock();
        $channelResource->expects($this->once())->method('queue_declare');
        $channel = $this->getMockBuilder('\FMUP\Queue\Channel')
            ->setMethods(array('setResource', 'hasResource'))
            ->setConstructorArgs(array('test'))
            ->getMock();
        $channel->expects($this->once())->method('setResource')->with($this->equalTo($channelResource));
        $channel->method('hasResource')->willReturnOnConsecutiveCalls(false, true);
        $amqpConnection = $this->getMockBuilder('\Tests\Queue\Driver\AMQPStreamConnectionMockQueueDriverAmqp')
            ->setMethods(array('channel'))
            ->getMock();
        $amqpConnection->expects($this->once())->method('channel')->willReturn($channelResource);
        $environment = $this->getMockBuilder('\Tests\Queue\Driver\EnvironmentMockQueueDriverAmqp')
            ->setMethods(array('get'))
            ->getMock();
        $environment->method('get')->willReturn('test');
        $amqp = $this->getMockBuilder('\FMUP\Queue\Driver\Amqp')
            ->setMethods(array('getAmqpConnection', 'hasEnvironment', 'getEnvironment'))
            ->getMock();
        $amqp->expects($this->once())->method('getAmqpConnection')->willReturn($amqpConnection);
        $amqp->method('hasEnvironment')->willReturn(true);
        $amqp->method('getEnvironment')->willReturn($environment);
        /** @var $amqp \FMUP\Queue\Driver\Amqp */
        /** @var $channel \FMUP\Queue\Channel */
        $this->assertSame($channel, $amqp->connect($channel));
        $this->assertSame($channel, $amqp->connect($channel));
    }

    public function testSetGetAmqpConnection()
    {
        $amqp = $this->getMockBuilder('\FMUP\Queue\Driver\Amqp')->setMethods(array('getDefaultConnection'))->getMock();
        $amqp->method('getDefaultConnection')->willReturn(new AMQPStreamConnectionMockQueueDriverAmqp);
        /** @var $amqp \FMUP\Queue\Driver\Amqp */
        $connection = $amqp->getAmqpConnection();
        $this->assertInstanceOf('\PhpAmqpLib\Connection\AMQPStreamConnection', $connection);
        $this->assertSame($connection, $amqp->getAmqpConnection());
        $newFakeInstance  = new AMQPStreamConnectionMockQueueDriverAmqp;
        $this->assertSame($amqp, $amqp->setAmqpConnection($newFakeInstance));
        $this->assertSame($newFakeInstance, $amqp->getAmqpConnection());
    }

    public function testGetStatsFails()
    {
        $amqp = new \FMUP\Queue\Driver\Amqp;
        $channel = $this->getMockBuilder('\FMUP\Queue\Channel')
            ->setMethods(null)
            ->setConstructorArgs(array('test'))
            ->getMock();
        /** @var $channel \FMUP\Queue\Channel */
        $this->setExpectedException('\FMUP\Queue\Exception', 'Stats not available on AMQP Driver');
        $amqp->getStats($channel);
    }

    public function testAckMessageFail()
    {
        $message = new \FMUP\Queue\Message();
        $message->setOriginal('test');
        $this->setExpectedException('\FMUP\Queue\Exception', 'Unable to ACK this mixed message. Need AMQPMessage');
        $amqp = new \FMUP\Queue\Driver\Amqp;
        $channel = $this->getMockBuilder('\FMUP\Queue\Channel')
            ->setMethods(null)
            ->setConstructorArgs(array('test'))
            ->getMock();
        /** @var $channel \FMUP\Queue\Channel */
        $amqp->ackMessage($channel, $message);
    }

    public function testAckMessageFailWhenCantConnect()
    {
        $channelResource = $this->getMockBuilder('\Tests\Queue\Driver\AMQPChannelMockQueueDriverAmqp')
            ->setMethods(array('basic_ack'))
            ->getMock();
        $channelResource->method('basic_ack')->with($this->equalTo(1));
        $amqpMessage = $this->getMockBuilder('\PhpAmqpLib\Message\AMQPMessage')->getMock();
        /** @var \PhpAmqpLib\Message\AMQPMessage $amqpMessage */
        $amqpMessage->delivery_info = array(
            'delivery_tag' => 1,
        );
        $message = new \FMUP\Queue\Message();
        $message->setOriginal($amqpMessage);
        $channel = $this->getMockBuilder('\FMUP\Queue\Channel')
            ->setMethods(array('hasResource', 'getResource'))
            ->setConstructorArgs(array('test'))
            ->getMock();
        $channel->method('hasResource')->willReturn(false);
        $channel->method('getResource')->willReturn(false);
        $amqp = $this->getMockBuilder('\FMUP\Queue\Driver\Amqp')->setMethods(array('getQueue', 'connect'))->getMock();
        $amqp->method('getQueue')->willReturn($channelResource);
        $amqp->expects($this->once())->method('connect')->with($this->equalTo($channel));
        /** @var $amqp \FMUP\Queue\Driver\Amqp */
        /** @var $channel \FMUP\Queue\Channel */
        $this->setExpectedException('\FMUP\Queue\Exception', 'Resource is not AMQPChannel');
        $amqp->ackMessage($channel, $message);
    }

    public function testAckMessage()
    {
        $channelResource = $this->getMockBuilder('\Tests\Queue\Driver\AMQPChannelMockQueueDriverAmqp')
            ->setMethods(array('basic_ack'))
            ->getMock();
        $channelResource->expects($this->once())->method('basic_ack')->with($this->equalTo(1));
        $amqpMessage = $this->getMockBuilder('\PhpAmqpLib\Message\AMQPMessage')->getMock();
        /** @var \PhpAmqpLib\Message\AMQPMessage $amqpMessage */
        $amqpMessage->delivery_info = array(
            'delivery_tag' => 1,
        );
        $message = new \FMUP\Queue\Message();
        $message->setOriginal($amqpMessage);
        $channel = $this->getMockBuilder('\FMUP\Queue\Channel')
            ->setMethods(array('hasResource', 'getResource'))
            ->setConstructorArgs(array('test'))
            ->getMock();
        $channel->method('hasResource')->willReturn(true);
        $channel->method('getResource')->willReturn($channelResource);
        $amqp = $this->getMockBuilder('\FMUP\Queue\Driver\Amqp')->setMethods(array('getQueue'))->getMock();
        $amqp->method('getQueue')->willReturn($channelResource);
        /** @var $amqp \FMUP\Queue\Driver\Amqp */
        /** @var $channel \FMUP\Queue\Channel */
        $amqp->ackMessage($channel, $message);
    }

    public function testPush()
    {
        $amqpMessage = $this->getMockBuilder('\PhpAmqpLib\Message\AMQPMessage')->getMock();
        $channelResource = $this->getMockBuilder('\Tests\Queue\Driver\AMQPChannelMockQueueDriverAmqp')
            ->setMethods(array('basic_publish'))
            ->getMock();
        $channelResource->method('basic_publish')->with($this->equalTo($amqpMessage), $this->equalTo(''), $this->equalTo('test'));
        $settings = $this->getMockBuilder('\FMUP\Queue\Channel\Settings')
            ->setMethods(array('getSerialize'))
            ->getMock();
        $settings->expects($this->once())->method('getSerialize')->willReturn(false);
        /** @var \PhpAmqpLib\Message\AMQPMessage $amqpMessage */
        $channel = $this->getMockBuilder('\FMUP\Queue\Channel')
            ->setMethods(array('hasResource', 'getResource', 'getSettings'))
            ->setConstructorArgs(array('test'))
            ->getMock();
        $channel->method('hasResource')->willReturn(true);
        $channel->method('getResource')->willReturn($channelResource);
        $channel->method('getSettings')->willReturn($settings);
        $amqp = $this->getMockBuilder('\FMUP\Queue\Driver\Amqp')->setMethods(array('getQueue'))->getMock();
        $amqp->method('getQueue')->willReturn($channelResource);
        /** @var $amqp \FMUP\Queue\Driver\Amqp */
        /** @var $channel \FMUP\Queue\Channel */
        $this->assertTrue($amqp->push($channel, $amqpMessage));
    }

    public function testPushNotMessageAndSerialize()
    {
        $amqpMessage = new \PhpAmqpLib\Message\AMQPMessage(serialize('test'));
        $channelResource = $this->getMockBuilder('\Tests\Queue\Driver\AMQPChannelMockQueueDriverAmqp')
            ->setMethods(array('basic_publish'))
            ->getMock();
        $channelResource->method('basic_publish')
            ->with($this->equalTo($amqpMessage), $this->equalTo(''), $this->equalTo('test'));
        $settings = $this->getMockBuilder('\FMUP\Queue\Channel\Settings')
            ->setMethods(array('getSerialize'))
            ->getMock();
        $settings->expects($this->once())->method('getSerialize')->willReturn(true);
        /** @var \PhpAmqpLib\Message\AMQPMessage $amqpMessage */
        $channel = $this->getMockBuilder('\FMUP\Queue\Channel')
            ->setMethods(array('hasResource', 'getResource', 'getSettings'))
            ->setConstructorArgs(array('test'))
            ->getMock();
        $channel->method('hasResource')->willReturn(true);
        $channel->method('getResource')->willReturn($channelResource);
        $channel->method('getSettings')->willReturn($settings);
        $amqp = $this->getMockBuilder('\FMUP\Queue\Driver\Amqp')->setMethods(array('getQueue'))->getMock();
        $amqp->method('getQueue')->willReturn($channelResource);
        /** @var $amqp \FMUP\Queue\Driver\Amqp */
        /** @var $channel \FMUP\Queue\Channel */
        $this->assertTrue($amqp->push($channel, 'test'));
    }

    public function testPullWhenBlockReceiveIsOn()
    {
        $channelResource = $this->getMockBuilder('\Tests\Queue\Driver\AMQPChannelMockQueueDriverAmqp')
            ->setMethods(array('basic_consume', 'wait'))
            ->getMock();
        $channelResource->method('basic_consume')->with(
            $this->equalTo('test'),
            $this->equalTo(''),
            $this->equalTo(false),
            $this->equalTo(true), //autoAck
            $this->equalTo(false),
            $this->equalTo(false)
        )
        ->will($this->returnCallback(function ($name, $thing, $false, $autoAck, $other, $else, $callback) {
            $message = new AMQPMessage();
            $message->body = 'hello';
            call_user_func_array($callback, array($message));
        }));
        $settings = $this->getMockBuilder('\FMUP\Queue\Channel\Settings')
            ->setMethods(array('getBlockReceive', 'getAutoAck', 'getSerialize'))
            ->getMock();
        $settings->expects($this->once())->method('getBlockReceive')->willReturn(true);
        $settings->expects($this->once())->method('getAutoAck')->willReturn(true);
        $settings->expects($this->once())->method('getSerialize')->willReturn(false);
        /** @var \PhpAmqpLib\Message\AMQPMessage $amqpMessage */
        $channel = $this->getMockBuilder('\FMUP\Queue\Channel')
            ->setMethods(array('hasResource', 'getResource', 'getSettings'))
            ->setConstructorArgs(array('test'))
            ->getMock();
        $channel->method('hasResource')->willReturn(true);
        $channel->method('getResource')->willReturn($channelResource);
        $channel->method('getSettings')->willReturn($settings);
        $amqp = $this->getMockBuilder('\FMUP\Queue\Driver\Amqp')
            ->setMethods(array('getQueue'))
            ->getMock();
        $amqp->method('getQueue')->willReturn($channelResource);
        /** @var $amqp \FMUP\Queue\Driver\Amqp */
        /** @var $channel \FMUP\Queue\Channel */
        $message = new AMQPMessage();
        $message->body = 'hello';
        $expectedMessage = new \FMUP\Queue\Message();
        $expectedMessage->setOriginal($message)->setTranslated($message->body);
        $this->assertEquals($expectedMessage, $amqp->pull($channel));
    }

    public function testPullWhenBlockReceiveIsOff()
    {
        $channelResource = $this->getMockBuilder('\Tests\Queue\Driver\AMQPChannelMockQueueDriverAmqp')
            ->setMethods(array('basic_get'))
            ->getMock();
        $channelResource->method('basic_get')->with($this->equalTo('test'), $this->equalTo(true))->willReturn(new AMQPMessage());
        $settings = $this->getMockBuilder('\FMUP\Queue\Channel\Settings')
            ->setMethods(array('getBlockReceive', 'getSerialize'))
            ->getMock();
        $settings->expects($this->once())->method('getBlockReceive')->willReturn(false);
        /** @var \PhpAmqpLib\Message\AMQPMessage $amqpMessage */
        $channel = $this->getMockBuilder('\FMUP\Queue\Channel')
            ->setMethods(array('hasResource', 'getResource', 'getSettings'))
            ->setConstructorArgs(array('test'))
            ->getMock();
        $channel->method('hasResource')->willReturn(true);
        $channel->method('getResource')->willReturn($channelResource);
        $channel->method('getSettings')->willReturn($settings);
        $amqp = $this->getMockBuilder('\FMUP\Queue\Driver\Amqp')->setMethods(array('getQueue'))->getMock();
        $amqp->method('getQueue')->willReturn($channelResource);
        /** @var $amqp \FMUP\Queue\Driver\Amqp */
        /** @var $channel \FMUP\Queue\Channel */
        $message = new AMQPMessage();
        $expectedMessage = new \FMUP\Queue\Message();
        $expectedMessage->setOriginal($message)->setTranslated($message->body);
        $this->assertEquals($expectedMessage, $amqp->pull($channel));
    }
}
