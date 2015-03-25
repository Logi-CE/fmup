<?php
namespace FMUP;

/**
 * Class Session
 * @package FMUP
 */
class Session
{
    const SESSION_STARTED = true;
    const SESSION_NOT_STARTED = false;

    private $sessionState;
    private static $instance;

    private function __construct()
    {
    }

    private function __clone()
    {

    }

    private function __sleep()
    {

    }

    private function __wakeup()
    {

    }

    /**
     * Retrieve session system - start session if not started
     * @param string $name
     * @return Session
     */
    public static function getInstance($name = null)
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }

        self::$instance->start($name);

        return self::$instance;
    }

    /**
     * Check whether session is started
     * @return bool
     */
    public function isStarted()
    {
        if (is_null($this->sessionState)){
            if (version_compare(phpversion(), '5.4.0', '>=')) {
                $this->sessionState = (session_status() === PHP_SESSION_ACTIVE ? self::SESSION_STARTED : self::SESSION_NOT_STARTED);
            } else {
                $this->sessionState = (session_id() === '' ? self::SESSION_NOT_STARTED : self::SESSION_STARTED);
            }
        }
        return $this->sessionState == self::SESSION_STARTED;
    }

    /**
     * Start session if not started and return if session is started
     * @param string $name
     * @return bool
     */
    public function start($name = null)
    {
        if (!$this->isStarted()) {
            session_name($name);
            $this->sessionState = session_start();
        }

        return $this->sessionState;
    }

    /**
     * Retrieve a session value
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        return $this->has($name) ? $_SESSION[$name] : null;
    }

    /**
     * Check whether a specific information exists in session
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, $_SESSION);
    }

    /**
     * Define a specific value in session
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function set($name, $value)
    {
        $_SESSION[$name] = $value;
        return $this;
    }

    /**
     * Delete a specific information from session
     * @param string $name
     * @return $this
     */
    public function remove($name)
    {
        if ($this->has($name)) {
            unset($_SESSION[$name]);
        }
        return $this;
    }

    /**
     * Destroy current session
     * @return bool
     */
    public function destroy()
    {
        if ($this->isStarted()) {
            $this->sessionState = !session_destroy();
            unset($_SESSION);

            return !$this->sessionState;
        }

        return self::SESSION_NOT_STARTED;
    }
}
