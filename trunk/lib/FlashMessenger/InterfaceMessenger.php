<?php
namespace FMUP\FlashMessenger;

/**
 * Defines methods to implement for each driver
 * @author sweffling
 */
interface InterfaceMessenger
{
    /**
     * Add a message in driver
     * @param \FMUP\FlashMessenger\Message $flash
     * @return \FMUP\FlashMessenger\Driver\Session
     */
    public function add(Message $flash);
    
    /**
     * Get all the messages in the driver
     * @return array $flashes
     */
    public function get();
    
    /**
     * Clear the driver from messages
     * @return \FMUP\FlashMessenger\Driver\Session
     */
    public function clear();
}
