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
    private $name;
    private $id;
    private static $instance;

    private function __construct()
    {
    }

    private function __clone()
    {

    }

    /**
     * @param bool $deleteOldSession
     * @return $this
     */
    public function regenerate($deleteOldSession = false)
    {
        session_regenerate_id((bool)$deleteOldSession);
        return $this;
    }

    /**
     * Retrieve session system - start session if not started
     * @return Session
     */
    final public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $class = get_called_class();
            self::$instance = new $class;
        }
        return self::$instance;
    }

    /**
     * Define session name
     * @param string $name
     * @throws \FMUP\Exception if session name defined contain only numbers
     * @return $this
     */
    public function setName($name)
    {
        if (!$this->isStarted()) {
            if (is_numeric($name)) {
                throw new Exception('Session name could not contain only numbers');
            }
            $this->name = (string)$name;
        }
        return $this;
    }

    /**
     * Retrieve session name
     * @return string|null
     */
    public function getName()
    {
        if ($this->isStarted()) {
            $this->name = session_name();
        }
        return $this->name;
    }

    /**
     * @param $id
     * @return $this
     * @throws Exception
     */
    public function setId($id)
    {
        if (!$this->isStarted()) {
            if (preg_match('/^[-,a-zA-Z0-9]{1,128}$/', $id)) {
                throw new Exception('Session name could not anything but letters + numbers');
            }
            $this->id = (string)$id;
        }
        return $this;
    }

    /**
     * Retrieve session name
     * @return string|null
     */
    public function getId()
    {
        if ($this->isStarted()) {
            $this->id = session_id();
        }
        return $this->id;
    }

    /**
     * Check whether session is started
     * @return bool
     */
    public function isStarted()
    {
        if (is_null($this->sessionState)) {
            if (version_compare(phpversion(), '5.4.0', '>=')) {
                $this->sessionState = (
                    session_status() === PHP_SESSION_ACTIVE ? self::SESSION_STARTED : self::SESSION_NOT_STARTED
                );
            } else {
                $this->sessionState = (session_id() === '' ? self::SESSION_NOT_STARTED : self::SESSION_STARTED);
            }
        }
        return $this->sessionState == self::SESSION_STARTED;
    }

    /**
     * Start session if not started and return if session is started
     * @return bool
     */
    public function start()
    {
        if (!$this->isStarted()) {
            if ($this->getName()) {
                session_name($this->getName());
            }
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
        $this->start();
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
        $this->start();
        $_SESSION[$name] = $value;
        return $this;
    }

    /**
     * Forget all values in session without destructing it
     * @return $this
     */
    public function clear()
    {
        $this->start();
        $_SESSION = array();
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
