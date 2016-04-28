<?php
/**
 * SessionTest.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\FlashMessenger\Driver;

class SessionMock extends \FMUP\Session
{
    private $memory = array();
    public function __construct()
    {
    }

    public function get($name)
    {
        return isset($this->memory[$name]) ? $this->memory[$name] : null;
    }

    public function set($name, $value)
    {
        $this->memory[$name] = $value;
        return $this;
    }

    public function remove($name)
    {
        unset($this->memory[$name]);
        return $this;
    }
}

class SessionTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGetSession()
    {
        $sessionMock = $this->getMock(SessionMock::class, null);
        /** @var $sessionMock SessionMock */
        $session = new \FMUP\FlashMessenger\Driver\Session();
        $reflection = new \ReflectionMethod(\FMUP\FlashMessenger\Driver\Session::class, 'getSession');
        $reflection->setAccessible(true);
        $this->assertInstanceOf(\FMUP\Session::class, $reflection->invoke($session));
        $this->assertSame($session, $session->setSession($sessionMock));
        $this->assertSame($sessionMock, $reflection->invoke($session));
    }

    public function testAddGetClear()
    {
        $sessionMock = $this->getMock(SessionMock::class, null);
        $message = new \FMUP\FlashMessenger\Message('test');
        $message2 = new \FMUP\FlashMessenger\Message('test2');
        /** @var $sessionMock SessionMock */
        $session = new \FMUP\FlashMessenger\Driver\Session();
        $this->assertSame($session, $session->setSession($sessionMock)->add($message));
        $this->assertSame(array($message), $session->get());
        $this->assertSame($session, $session->setSession($sessionMock)->add($message2));
        $this->assertSame(array($message, $message2), $session->get());
        $this->assertSame($session, $session->clear());
        $this->assertNull($session->get());
    }
}
