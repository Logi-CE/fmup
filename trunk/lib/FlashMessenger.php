<?php

namespace FMUP;

class FlashMessenger
{

    private static $instance = null;
    private $driver;

    /**
     * Set the singleton messenger instance
     * @return Messenger $instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get the driver to stock messages in
     * @return FlashMessenger\Driver\Session $driver
     */
    public function getDriver()
    {
        if($this->driver === null){
            $this->driver = new FlashMessenger\Driver\Session();
        }
        return $this->driver;
    }

    /**
     * Set a flash in session
     * @return Message
     */
    public function add(FlashMessenger\Message $flash)
    {
        $this->getDriver()->add($flash);
        return $this;
    }

    /**
     * Get all flashes messages
     * @return array|null flashes
     */
    public function get()
    {
        $flashes = $this->getDriver()->get();
        $this->clear();
        return $flashes;
    }

    /**
     * Unset all flashes from driver
     */
    public function clear()
    {
        $this->getDriver()->clear();
        return $this;
    }

}
