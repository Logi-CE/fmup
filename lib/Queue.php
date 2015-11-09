<?php
namespace FMUP;

class Queue
{
    use Environment\OptionalTrait;

    private $name;
    private $driver;
    private $queueResource;


    /**
     * Creates a queue
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = (string)$name;
    }

    /**
     * Return current driver
     * @return Queue\DriverInterface
     */
    public function getDriver()
    {
        if (!$this->driver) {
            $this->driver = new Queue\Driver\Native();
            if ($this->hasEnvironment()) {
                $this->driver->setEnvironment($this->getEnvironment());
            }
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
     * Retrieve queue resource
     * @return resource
     */
    private function getQueueResource()
    {
        if (!$this->queueResource) {
            $this->queueResource = $this->getDriver()->connect($this->name);
        }
        return $this->queueResource;
    }

    /**
     * Get a message from current queue
     * @param string $messageType (optional message type requested)
     * @return mixed|null null if no message
     */
    public function pull($messageType = null)
    {
        return $this->getDriver()->pull($this->getQueueResource(), $messageType);
    }

    /**
     * Puts a message in a queue
     * @param mixed $message
     * @param string $messageType (optional message type)
     * @return bool
     */
    public function push($message, $messageType = null)
    {
        return $this->getDriver()->push($this->getQueueResource(), $message, $messageType);
    }

    /**
     * @return array
     */
    public function getStats()
    {
        return $this->getDriver()->getStats($this->getQueueResource());
    }
}
