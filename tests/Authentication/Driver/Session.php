<?php
/**
 * Session.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Authentication\Driver;

class SessionMockAuthenticationDriver extends \FMUP\Session
{
    public function __construct()
    {
    }
}

class SessionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSetSession()
    {
        $driver = new \FMUP\Authentication\Driver\Session();
        $session = $driver->getSession();
        $this->assertInstanceOf(\FMUP\Session::class, $session);
        $this->assertSame($session, $driver->getSession());
        $newSession = new SessionMockAuthenticationDriver();
        $this->assertSame($driver, $driver->setSession($newSession));
        $this->assertSame($newSession, $driver->getSession());
    }

    public function testSetGetClear()
    {
        $user = $this->getMockBuilder(\FMUP\Authentication\UserInterface::class)->getMock();
        $session = $this->getMockBuilder(SessionMockAuthenticationDriver::class)
            ->setMethods(array('set', 'get', 'remove'))
            ->getMock();
        $session->expects($this->exactly(1))->method('get')->willReturn($user);
        $session->expects($this->exactly(1))->method('set');
        $session->expects($this->exactly(1))->method('remove');
        /**
         * @var $session SessionMockAuthenticationDriver
         * @var $user \FMUP\Authentication\UserInterface
         */
        $driver = new \FMUP\Authentication\Driver\Session();
        $this->assertSame($driver, $driver->setSession($session)->set($user));
        $this->assertSame($user, $driver->get());
        $this->assertSame($driver, $driver->clear());
    }
}
