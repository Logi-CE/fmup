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
     * Creates a queue if don't exists / Connect a queue
     * @param Channel $channel
     * @return Channel
     */
    public function connect(Channel $channel);

    /**
     * Get a message from a queue
     * @param Channel $channel
     * @param string $messageType
     * @return Message|null
     */
    public function pull(Channel $channel, $messageType = null);

    /**
     * @param Channel $channel
     * @param mixed $message
     * @param string $messageType
     * @return bool true on success
     */
    public function push(Channel $channel, $message, $messageType = null);

    /**
     * @param Channel $channel
     * @return array
     */
    public function getStats(Channel $channel);

    /**
     * Acknowledge a message
     * @param Channel $channel
     * @param Message $message
     * @return $this
     */
    public function ackMessage(Channel $channel, Message $message);
}
