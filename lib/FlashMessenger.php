<?php
namespace FMUP;

/**
 * Class FlashMessenger
 * @package FMUP
 */
class FlashMessenger
{

    private static $instance = null;
    private $driver;

    /**
     * Set the singleton messenger instance
     * @return FlashMessenger
     */
    final public static function getInstance()
    {
        if (self::$instance === null) {
            $class = get_called_class();
            self::$instance = new $class;
        }
        return self::$instance;
    }

    /**
     * Get the driver to stock messages in
     * @return FlashMessenger\DriverInterface
     */
    public function getDriver()
    {
        if ($this->driver === null) {
            $this->driver = new FlashMessenger\Driver\Session();
        }
        return $this->driver;
    }

    /**
     * Change driver used for flashmessenger
     * @param FlashMessenger\DriverInterface $driver
     * @return $this
     */
    public function setDriver(FlashMessenger\DriverInterface $driver)
    {
        $this->driver = $driver;
        return $this;
    }

    /**
     * Set a flash in session
     * @param FlashMessenger\Message $flash
     * @return $this
     */
    public function add(FlashMessenger\Message $flash)
    {
        $this->getDriver()->add($flash);
        return $this;
    }

    /**
     * Get all flashes messages
     * @return array|null
     */
    public function get()
    {
        $flashes = $this->getDriver()->get();
        $this->clear();
        return $flashes;
    }

    /**
     * Unset all flashes from driver
     * @return $this
     */
    public function clear()
    {
        $this->getDriver()->clear();
        return $this;
    }

}
