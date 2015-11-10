<?php
namespace FMUP\Queue;
use FMUP\Environment;

/**
 * Interface DriverInterface
 * @package FMUP\Queue
 */
interface DriverInterface
{
    /**
     * Check if a queue name exists
     * @param string $name
     * @return bool
     */
    public function exists($name);

    /**
     * Creates a queue if don't exists / Connect a queue
     * @param string $name
     * @return resource
     */
    public function connect($name);

    /**
     * Get a message from a queue
     * @param resource $queueResource
     * @param string $messageType
     * @return mixed
     */
    public function pull($queueResource, $messageType = null);

    /**
     * @param resource $queueResource
     * @param mixed $message
     * @param string $messageType
     * @return bool true on success
     */
    public function push($queueResource, $message, $messageType = null);

    /**
     * @param resource $queueResource
     * @return array
     */
    public function getStats($queueResource);
}
