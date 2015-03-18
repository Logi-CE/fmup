<?php
namespace FMUP\FlashMessenger\Driver;

/**
 * Description of Session
 *
 * @author sweffling
 */
class Session implements \FMUP\FlashMessenger\InterfaceMessenger
{
    /**
     * Add a message in session
     * @param \FMUP\FlashMessenger\Message $flash
     * @return \FMUP\FlashMessenger\Driver\Session
     */
    public function add(\FMUP\FlashMessenger\Message $flash)
    {
        $_SESSION['flashMessenger'][] = $flash;
        return $this;
    }
    
    /**
     * Get all the messages in the session
     * @return array $flashes
     */
    public function get()
    {
        return isset($_SESSION['flashMessenger']) ? $_SESSION['flashMessenger'] : null;
    }

    /**
     * Clear the session from messages
     * @return \FMUP\FlashMessenger\Driver\Session
     */
    public function clear()
    {
        if (isset($_SESSION['flashMessenger'])) {
            unset($_SESSION['flashMessenger']);
        }
        return $this;
    }
    
}
