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
        $channelResource = $this->getMock(AMQPChannelMockQueueDriverAmqp::class, array('queue_declare'));
        $channelResource->expects($this->once())->method('queue_declare');
        $channel = $this->getMock(\FMUP\Queue\Channel::class, array('setResource', 'hasResource'), array('test'));
        $channel->expects($this->once())->method('setResource')->with($this->equalTo($channelResource));
        $channel->method('hasResource')->willReturnOnConsecutiveCalls(false, true);
        $amqpConnection = $this->getMock(AMQPStreamConnectionMockQueueDriverAmqp::class, array('channel'));
        $amqpConnection->expects($this->once())->method('channel')->willReturn($channelResource);
        $environment = $this->getMock(EnvironmentMockQueueDriverAmqp::class, array('get'));
        $environment->method('get')->willReturn('test');
        $amqp = $this->getMock(\FMUP\Queue\Driver\Amqp::class, array('getAmqpConnection', 'hasEnvironment', 'getEnvironment'));
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
        $amqp = $this->getMock(\FMUP\Queue\Driver\Amqp::class, array('getDefaultConnection'));
        $amqp->method('getDefaultConnection')->willReturn(new AMQPStreamConnectionMockQueueDriverAmqp);
        /** @var $amqp \FMUP\Queue\Driver\Amqp */
        $connection = $amqp->getAmqpConnection();
        $this->assertInstanceOf(\PhpAmqpLib\Connection\AMQPStreamConnection::class, $connection);
        $this->assertSame($connection, $amqp->getAmqpConnection());
        $newFakeInstance  = new AMQPStreamConnectionMockQueueDriverAmqp;
        $this->assertSame($amqp, $amqp->setAmqpConnection($newFakeInstance));
        $this->assertSame($newFakeInstance, $amqp->getAmqpConnection());
    }

    public function testGetStatsFails()
    {
        $amqp = new \FMUP\Queue\Driver\Amqp;
        $channel = $this->getMock(\FMUP\Queue\Channel::class, null, array('test'));
        /** @var $channel \FMUP\Queue\Channel */
        $this->expectException(\FMUP\Queue\Exception::class);
        $this->expectExceptionMessage('Stats not available on AMQP Driver');
        $amqp->getStats($channel);
    }

    public function testAckMessageFail()
    {
        $message = new \FMUP\Queue\Message();
        $message->setOriginal('test');
        $this->expectException(\FMUP\Queue\Exception::class);
        $this->expectExceptionMessage('Unable to ACK this mixed message. Need AMQPMessage');
        $amqp = new \FMUP\Queue\Driver\Amqp;
        $channel = $this->getMock(\FMUP\Queue\Channel::class, null, array('test'));
        /** @var $channel \FMUP\Queue\Channel */
        $amqp->ackMessage($channel, $message);
    }

    public function testAckMessageFailWhenCantConnect()
    {
        $channelResource = $this->getMock(AMQPChannelMockQueueDriverAmqp::class, array('basic_ack'));
        $channelResource->method('basic_ack')->with($this->equalTo(1));
        $amqpMessage = $this->getMock(\PhpAmqpLib\Message\AMQPMessage::class);
        /** @var \PhpAmqpLib\Message\AMQPMessage $amqpMessage */
        $amqpMessage->delivery_info = array(
            'delivery_tag' => 1,
        );
        $message = new \FMUP\Queue\Message();
        $message->setOriginal($amqpMessage);
        $channel = $this->getMock(\FMUP\Queue\Channel::class, array('hasResource', 'getResource'), array('test'));
        $channel->method('hasResource')->willReturn(false);
        $channel->method('getResource')->willReturn(false);
        $amqp = $this->getMock(\FMUP\Queue\Driver\Amqp::class, array('getQueue', 'connect'));
        $amqp->method('getQueue')->willReturn($channelResource);
        $amqp->expects($this->once())->method('connect')->with($this->equalTo($channel));
        /** @var $amqp \FMUP\Queue\Driver\Amqp */
        /** @var $channel \FMUP\Queue\Channel */
        $this->expectException(\FMUP\Queue\Exception::class);
        $this->expectExceptionMessage('Resource is not AMQPChannel');
        $amqp->ackMessage($channel, $message);
    }

    public function testAckMessage()
    {
        $channelResource = $this->getMock(AMQPChannelMockQueueDriverAmqp::class, array('basic_ack'));
        $channelResource->expects($this->once())->method('basic_ack')->with($this->equalTo(1));
        $amqpMessage = $this->getMock(\PhpAmqpLib\Message\AMQPMessage::class);
        /** @var \PhpAmqpLib\Message\AMQPMessage $amqpMessage */
        $amqpMessage->delivery_info = array(
            'delivery_tag' => 1,
        );
        $message = new \FMUP\Queue\Message();
        $message->setOriginal($amqpMessage);
        $channel = $this->getMock(\FMUP\Queue\Channel::class, array('hasResource', 'getResource'), array('test'));
        $channel->method('hasResource')->willReturn(true);
        $channel->method('getResource')->willReturn($channelResource);
        $amqp = $this->getMock(\FMUP\Queue\Driver\Amqp::class, array('getQueue'));
        $amqp->method('getQueue')->willReturn($channelResource);
        /** @var $amqp \FMUP\Queue\Driver\Amqp */
        /** @var $channel \FMUP\Queue\Channel */
        $amqp->ackMessage($channel, $message);
    }

    public function testPush()
    {
        $amqpMessage = $this->getMock(\PhpAmqpLib\Message\AMQPMessage::class);
        $channelResource = $this->getMock(AMQPChannelMockQueueDriverAmqp::class, array('basic_publish'));
        $channelResource->method('basic_publish')->with($this->equalTo($amqpMessage), $this->equalTo(''), $this->equalTo('test'));
        $settings = $this->getMock(\FMUP\Queue\Channel\Settings::class, array('getSerialize'));
        $settings->expects($this->once())->method('getSerialize')->willReturn(false);
        /** @var \PhpAmqpLib\Message\AMQPMessage $amqpMessage */
        $channel = $this->getMock(\FMUP\Queue\Channel::class, array('hasResource', 'getResource', 'getSettings'), array('test'));
        $channel->method('hasResource')->willReturn(true);
        $channel->method('getResource')->willReturn($channelResource);
        $channel->method('getSettings')->willReturn($settings);
        $amqp = $this->getMock(\FMUP\Queue\Driver\Amqp::class, array('getQueue'));
        $amqp->method('getQueue')->willReturn($channelResource);
        /** @var $amqp \FMUP\Queue\Driver\Amqp */
        /** @var $channel \FMUP\Queue\Channel */
        $this->assertTrue($amqp->push($channel, $amqpMessage));
    }

    public function testPullWhenBlockReceiveIsOn()
    {
        $channelResource = $this->getMock(AMQPChannelMockQueueDriverAmqp::class, array('basic_consume', 'wait'));
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
        $settings = $this->getMock(\FMUP\Queue\Channel\Settings::class, array('getBlockReceive', 'getAutoAck', 'getSerialize'));
        $settings->expects($this->once())->method('getBlockReceive')->willReturn(true);
        $settings->expects($this->once())->method('getAutoAck')->willReturn(true);
        $settings->expects($this->once())->method('getSerialize')->willReturn(false);
        /** @var \PhpAmqpLib\Message\AMQPMessage $amqpMessage */
        $channel = $this->getMock(\FMUP\Queue\Channel::class, array('hasResource', 'getResource', 'getSettings'), array('test'));
        $channel->method('hasResource')->willReturn(true);
        $channel->method('getResource')->willReturn($channelResource);
        $channel->method('getSettings')->willReturn($settings);
        $amqp = $this->getMock(\FMUP\Queue\Driver\Amqp::class, array('getQueue'));
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
        $channelResource = $this->getMock(AMQPChannelMockQueueDriverAmqp::class, array('basic_get'));
        $channelResource->method('basic_get')->with($this->equalTo('test'), $this->equalTo(true))->willReturn(new AMQPMessage());
        $settings = $this->getMock(\FMUP\Queue\Channel\Settings::class, array('getBlockReceive', 'getSerialize'));
        $settings->expects($this->once())->method('getBlockReceive')->willReturn(false);
        /** @var \PhpAmqpLib\Message\AMQPMessage $amqpMessage */
        $channel = $this->getMock(\FMUP\Queue\Channel::class, array('hasResource', 'getResource', 'getSettings'), array('test'));
        $channel->method('hasResource')->willReturn(true);
        $channel->method('getResource')->willReturn($channelResource);
        $channel->method('getSettings')->willReturn($settings);
        $amqp = $this->getMock(\FMUP\Queue\Driver\Amqp::class, array('getQueue'));
        $amqp->method('getQueue')->willReturn($channelResource);
        /** @var $amqp \FMUP\Queue\Driver\Amqp */
        /** @var $channel \FMUP\Queue\Channel */
        $message = new AMQPMessage();
        $expectedMessage = new \FMUP\Queue\Message();
        $expectedMessage->setOriginal($message)->setTranslated($message->body);
        $this->assertEquals($expectedMessage, $amqp->pull($channel));
    }
}
