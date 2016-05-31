<?php
namespace FMUP\FlashMessenger;

/**
 * Defines methods to implement for each driver
 * @author sweffling
 */
interface DriverInterface
{
    /**
     * Add a message in driver
     * @param Message $flash
     * @return $this
     */
    public function add(Message $flash);

    /**
     * Get all the messages in the driver
     * @return Message[]|null $flashes
     */
    public function get();

    /**
     * Clear the driver from messages
     * @return $this
     */
    public function clear();
}
