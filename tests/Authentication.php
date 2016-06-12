<?php
/**
 * Authentication.php
 * @author: jmoulin@castelis.com
 */

namespace Tests;

class AuthenticationMock extends \FMUP\Authentication
{
    public function __construct()
    {

    }
}

class AuthenticationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetInstance()
    {
        $reflector = new \ReflectionClass('\FMUP\Authentication');
        $method = $reflector->getMethod('__construct');
        $this->assertTrue($method->isPrivate(), 'Construct must be private');
        try {
            $reflector->getMethod('__clone')->invoke(\FMUP\Authentication::getInstance());
            $this->fail('Clone must fail');
        } catch (\ReflectionException $e) {
            $this->assertEquals(
                'Trying to invoke private method FMUP\Authentication::__clone() from scope ReflectionMethod',
                $e->getMessage()
            );
        }

        $authentication = \FMUP\Authentication::getInstance();
        $this->assertInstanceOf('\FMUP\Authentication', $authentication);
        $authentication2 = \FMUP\Authentication::getInstance();
        $this->assertSame($authentication, $authentication2);
        return $authentication;
    }

    public function testSetGetDriver()
    {
        $auth = new AuthenticationMock();
        $defaultDriver = $auth->getDriver();
        $this->assertInstanceOf('\FMUP\Authentication\DriverInterface', $defaultDriver);
        $this->assertInstanceOf('\FMUP\Authentication\Driver\Session', $defaultDriver);
        $this->assertSame($defaultDriver, $auth->getDriver());

        $driver = $this->getMockBuilder('\FMUP\Authentication\DriverInterface')
            ->setMethods(array('set', 'get', 'clear'))
            ->getMock();
        /** @var \FMUP\Authentication\DriverInterface $driver */
        $this->assertSame($auth, $auth->setDriver($driver));
        $this->assertSame($driver, $auth->getDriver());
    }

    public function testLogin()
    {
        $login = 'login';
        $password = 'password';
        $user = $this->getMockBuilder('\FMUP\Authentication\UserInterface')
            ->setMethods(array('auth'))
            ->getMock();
        $user->expects($this->exactly(2))
            ->method('auth')
            ->will($this->onConsecutiveCalls(false, true))
            ->with($this->equalTo($login), $this->equalTo($password));
        $auth = $this->getMockBuilder('\Tests\AuthenticationMock')->setMethods(array('set'))->getMock();
        $auth->expects($this->exactly(1))->method('set')->with($user);
        $driver = $this->getMockBuilder('\FMUP\Authentication\DriverInterface')
            ->setMethods(array('set', 'get', 'clear'))
            ->getMock();
        /**
         * @var \FMUP\Authentication\DriverInterface $driver
         * @var \FMUP\Authentication\UserInterface $user
         * @var AuthenticationMock $auth
         */
        $this->assertSame($auth, $auth->setDriver($driver));
        $this->assertFalse($auth->login($user, $login, $password));
        $this->assertTrue($auth->login($user, $login, $password));
    }

    public function testSetGetClear()
    {
        $user = $this->getMockBuilder('\FMUP\Authentication\UserInterface')->setMethods(array('auth'))->getMock();
        $auth = new AuthenticationMock;
        $driver = $this->getMockBuilder('\FMUP\Authentication\DriverInterface')
            ->setMethods(array('set', 'get', 'clear'))
            ->getMock();
        $driver->expects($this->exactly(1))->method('set')->with($user);
        $driver->expects($this->exactly(1))->method('get')->willReturn($user);
        $driver->expects($this->exactly(1))->method('clear');
        /**
         * @var \FMUP\Authentication\DriverInterface $driver
         * @var \FMUP\Authentication\UserInterface $user
         */
        $this->assertSame($auth, $auth->setDriver($driver));
        $this->assertSame($auth, $auth->set($user));
        $this->assertSame($user, $auth->get());
        $this->assertSame($auth, $auth->clear());
    }
}
