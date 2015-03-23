<?php
namespace FMUP\FlashMessenger\Driver;

use FMUP\FlashMessenger\DriverInterface;
use FMUP\FlashMessenger\Message;

/**
 * Description of Session
 *
 * @author sweffling
 */
class Session implements DriverInterface
{
    /**
     * Add a message in session
     * @param Message $flash
     * @return $this
     */
    public function add(Message $flash)
    {
        $_SESSION[__CLASS__][] = $flash;
        return $this;
    }

    /**
     * Get all the messages in the session
     * @return array|null $flashes
     */
    public function get()
    {
        return isset($_SESSION[__CLASS__]) ? $_SESSION[__CLASS__] : null;
    }

    /**
     * Clear the session from messages
     * @return $this
     */
    public function clear()
    {
        if (isset($_SESSION[__CLASS__])) {
            unset($_SESSION[__CLASS__]);
        }
        return $this;
    }
}
