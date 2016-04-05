<?php
namespace FMUP;

use FMUP\Queue\Channel;
use FMUP\Queue\Exception as QueueException;

class Queue
{
    use Environment\OptionalTrait;

    private $driver;
    private $channel;
    private $channelName;

    /**
     * Creates a queue
     * @param string $name
     */
    public function __construct($name)
    {
        $this->channelName = (string)$name;
    }

    /**
     * Instanciate a channel if not already defined or retrieve defined channel
     * @return Channel
     * @throws QueueException
     */
    public function getOrDefineChannel()
    {
        if (!$this->channel) {
            if (empty($this->channelName)) {
                throw new QueueException('Unable to create queue with no name');
            }
            $this->channel = new Channel((string)$this->channelName);
        }
        return $this->channel;
    }

    /**
     * @param Channel $channel
     * @return $this
     */
    public function setChannel(Channel $channel)
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * Return current driver
     * @return Queue\DriverInterface
     */
    public function getDriver()
    {
        if (!$this->driver) {
            $this->driver = new Queue\Driver\Native();
        }
        if ($this->driver instanceof Environment\OptionalTrait &&
            !$this->driver->hasEnvironment() &&
            $this->hasEnvironment()
        ) {
            $this->driver->setEnvironment($this->getEnvironment());
        }
        return $this->driver;
    }

    /**
     * Define driver to use
     * @param Queue\DriverInterface $driverInterface
     * @return $this
     */
    public function setDriver(Queue\DriverInterface $driverInterface)
    {
        $this->driver = $driverInterface;
        return $this;
    }

    /**
     * Get a message from current queue
     * @param string $messageType (optional message type requested)
     * @return mixed|null null if no message
     */
    public function pull($messageType = null)
    {
        return $this->getDriver()->pull($this->getOrDefineChannel(), $messageType);
    }

    /**
     * Puts a message in a queue
     * @param mixed $message
     * @param string $messageType (optional message type)
     * @return bool
     */
    public function push($message, $messageType = null)
    {
        return $this->getDriver()->push($this->getOrDefineChannel(), $message, $messageType);
    }

    /**
     * @return array
     */
    public function getStats()
    {
        return $this->getDriver()->getStats($this->getOrDefineChannel());
    }

    /**
     * Acknowledge a message
     * @param mixed $message
     * @return $this
     * @throws QueueException
     */
    public function ackMessage($message)
    {
        return $this->getDriver()->ackMessage($this->getOrDefineChannel(), $message);
    }
}
