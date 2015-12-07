<?php
namespace FMUP\Queue\Driver;

use FMUP\Environment;
use FMUP\Queue\Channel;
use FMUP\Queue\DriverInterface;
use FMUP\Queue\Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Amqp implements DriverInterface
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
            $channelResource->queue_declare($channel->getName(), false, true);
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
            $this->setAmqpConnection(new AMQPStreamConnection('localhost', 5672, 'guest', 'guest'));
        }
        return $this->amqpConnection;
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
            ? new AMQPMessage($serialize ? serialize($message) : (string)$message)
            : $message;
        $queue->basic_publish($msg, '', $channel->getName());
        return true;
    }

    /**
     * @param Channel $channel
     * @param null $messageType
     * @return null
     * @throws Exception
     */
    public function pull(Channel $channel, $messageType = null)
    {
        $queue = $this->getQueue($channel);
        $this->currentMsg = null;
        $this->currentChannel = $channel;
        $name = $channel->getName();
        if ($channel->getSettings()->getBlockReceive()) {
            $queue->basic_consume($name, '', false, false, false, false, array($this, 'onPull'));
            do {
                $queue->wait();
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
        $serialize = $this->currentChannel->getSettings()->getSerialize();
        $this->currentMsg = $serialize ? unserialize($msg->body) : $msg->body;
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
}
