<?php
namespace FMUP\Queue\Driver;

use FMUP\Environment;
use FMUP\Queue\Channel;
use FMUP\Queue\DriverInterface;
use FMUP\Queue\Exception;
use FMUP\Queue\Message;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Amqp implements DriverInterface, Environment\OptionalInterface
{
    use Environment\OptionalTrait;

    private $amqpConnection;
    private $currentMsg;

    /**
     * @var Channel
     */
    private $currentChannel;

    public function connect(Channel $channel)
    {
        if (!$channel->hasResource()) {
            $channelResource = $this->getAmqpConnection()->channel();
            $channel->setName($this->secureName($channel->getName()));
            $channelResource->queue_declare($channel->getName(), false, true, false, false);
            $channel->setResource($channelResource);
        }
        return $channel;
    }

    /**
     * @param string $name
     * @return string
     */
    private function secureName($name)
    {
        if ($this->hasEnvironment()) {
            $name .= '.' . $this->getEnvironment()->get();
        }
        return $name;
    }

    /**
     * @return AMQPStreamConnection
     */
    public function getAmqpConnection()
    {
        if (!$this->amqpConnection) {
            $this->setAmqpConnection($this->getDefaultConnection());
        }
        return $this->amqpConnection;
    }

    /**
     * @return AMQPStreamConnection
     * @codeCoverageIgnore
     */
    protected function getDefaultConnection()
    {
        return new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
    }

    /**
     * @param AMQPStreamConnection $connection
     * @return $this
     */
    public function setAmqpConnection(AMQPStreamConnection $connection)
    {
        $this->amqpConnection = $connection;
        return $this;
    }

    /**
     * @param Channel $channel
     * @return AMQPChannel
     * @throws Exception
     */
    private function getQueue(Channel $channel)
    {
        if (!$channel->hasResource()) {
            $this->connect($channel);
        }

        $queue = $channel->getResource();
        if (!$queue instanceof AMQPChannel) {
            throw new Exception('Resource is not AMQPChannel');
        }
        return $queue;
    }

    /**
     * @param Channel $channel
     * @param mixed $message
     * @param null $messageType
     * @return bool
     * @throws Exception
     */
    public function push(Channel $channel, $message, $messageType = null)
    {
        $queue = $this->getQueue($channel);
        $serialize = $channel->getSettings()->getSerialize();
        $msg = (!$message instanceof AMQPMessage)
            ? new AMQPMessage($serialize ? serialize($message) : (string)$message, array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT))
            : $message;
        $queue->basic_publish($msg, '', $channel->getName());
        return true;
    }

    /**
     * @param Channel $channel
     * @param null $messageType
     * @return Message|null
     * @throws Exception
     */
    public function pull(Channel $channel, $messageType = null)
    {
        $queue = $this->getQueue($channel);
        $this->currentMsg = null;
        $this->currentChannel = $channel;
        $name = $channel->getName();
        if ($channel->getSettings()->getBlockReceive()) {
            $queue->basic_consume(
                $name,
                '',
                false,
                $channel->getSettings()->getAutoAck(),
                false,
                false,
                array($this, 'onPull')
            );
            do {
                $queue->wait(null, false, 650);
            } while (is_null($this->currentMsg));
        } else {
            $message = $queue->basic_get($name, true);
            if (!is_null($message)) {
                $this->onPull($message);
            }
        }
        return $this->currentMsg;
    }

    /**
     * @param AMQPMessage $msg
     * @return $this
     */
    public function onPull(AMQPMessage $msg)
    {
        $message = new Message();
        $serialize = $this->currentChannel->getSettings()->getSerialize();
        $message->setTranslated($serialize ? unserialize($msg->body) : $msg->body);
        $message->setOriginal($msg);
        $this->currentMsg = $message;
        return $this;
    }

    /**
     * @param Channel $channel
     * @throws Exception
     * @return array
     */
    public function getStats(Channel $channel)
    {
        throw new Exception('Stats not available on AMQP Driver');
    }

    /**
     * Ack a specified message
     * @param Channel $channel
     * @param Message $message
     * @return $this
     * @throws Exception
     */
    public function ackMessage(Channel $channel, Message $message)
    {
        $originalMessage = $message->getOriginal();
        if (!$originalMessage instanceof AMQPMessage) {
            throw new Exception('Unable to ACK this mixed message. Need AMQPMessage');
        }
        $this->getQueue($channel)->basic_ack($originalMessage->delivery_info['delivery_tag']);
        return $this;
    }
}
