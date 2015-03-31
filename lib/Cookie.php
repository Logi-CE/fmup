<?php
namespace FMUP;

/**
 * Class Cookie
 * @package FMUP
 */
class Cookie
{
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
     * Retrieve cookie system - start cookie if not started
     * @param string $name
     * @return Cookie
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Check whether a specific information exists in cookie
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return isset($_COOKIE[$name]);
    }

    /**
     * Define a new cookie
     * @param string $name
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @return $this
     */
    public function set($name, $value, $expire = 0, $path = "/", $domain = "", $secure = false, $httponly = false)
    {
        $time = time();
        if ($expire < $time) {
            $expire = $time + $expire;
        }
        setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
        return $this;
    }

    /**
     * Retrieve a specific Cookie value
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        return $this->has($name) ? $_COOKIE[$name] : null;
    }

    /**
     * Remove a specific Cookie
     * @param string $name
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @return $this
     */
    public function remove($name, $path = "/", $domain = "", $secure = false)
    {
        if ($this->has($name)) {
            setcookie($name, "", time() - 3600, $path, $domain, $secure);
        }
        return $this;
    }

    /**
     * Remove all Cookies
     * @return $this
     */
    public function destroy()
    {
        $cookiesSet = array_keys($_COOKIE);
        foreach ($cookiesSet as $cookie) {
            $this->remove($cookie);
        }
        return $this;
    }
}
