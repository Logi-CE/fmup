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
    private $session;

    /**
     * @return \FMUP\Session
     */
    private function getSession()
    {
        if (!$this->session) {
            $this->session = \FMUP\Session::getInstance();
        }
        return $this->session;
    }

    /**
     * @param \FMUP\Session $session
     * @return $this
     */
    public function setSession(\FMUP\Session $session)
    {
        $this->session = $session;
        return $this;
    }

    /**
     * Add a message in session
     * @param Message $flash
     * @return $this
     */
    public function add(Message $flash)
    {
        $messages = (array)$this->getSession()->get(__CLASS__);
        array_push($messages, $flash);
        $this->getSession()->set(__CLASS__, $messages);
        return $this;
    }

    /**
     * Get all the messages in the session
     * @return array|null $flashes
     */
    public function get()
    {
        return $this->getSession()->get(__CLASS__);
    }

    /**
     * Clear the session from messages
     * @return $this
     */
    public function clear()
    {
        $this->getSession()->remove(__CLASS__);
        return $this;
    }
}
