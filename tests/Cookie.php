<?php

namespace Tests;

use FMUP\Cookie;

class CookieMock extends Cookie
{
    public function __construct()
    {

    }

    protected function setCookie(
        $name,
        $value,
        $expire = 0,
        $path = '/',
        $domain = '',
        $secure = false,
        $httpOnly = false
    ) {

    }
}

class CookieTest extends \PHPUnit_Framework_TestCase
{
    const WRONG_EXCEPTION_CODE = 'Wrong exception code.';
    const ERROR_NOT_INSTANCE_OF = 'Not an instance of %s';

    private function getCookieMock()
    {
        $cookie = $this->getMockBuilder(CookieMock::class)->setMethods(array('setCookie'))->getMock();
        $reflection = new \ReflectionProperty(\FMUP\Cookie::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue($cookie);
        return $cookie;
    }

    /**
     * @return Cookie
     */
    public function testGetInstance()
    {
        $reflector = new \ReflectionClass(\FMUP\Cookie::class);
        $method = $reflector->getMethod('__construct');
        $this->assertTrue($method->isPrivate(), 'Construct must be private');
        try {
            $reflector->getMethod('__clone')->invoke(\FMUP\Cookie::getInstance());
            $this->fail('Clone must fail');
        } catch (\ReflectionException $e) {
            $this->assertEquals(
                'Trying to invoke private method FMUP\Cookie::__clone() from scope ReflectionMethod',
                $e->getMessage()
            );
        }

        $cookie = Cookie::getInstance();
        $this->assertInstanceOf(\FMUP\Cookie::class, $cookie, sprintf(self::ERROR_NOT_INSTANCE_OF, \FMUP\Cookie::class));
        $cookie2 = Cookie::getInstance();
        $this->assertSame($cookie, $cookie2, sprintf(self::ERROR_NOT_INSTANCE_OF, \FMUP\Cookie::class));
        return $cookie;
    }

    /**
     * @depends testGetInstance
     * @param Cookie $cookie
     * @return Cookie
     */
    public function testHas(Cookie $cookie)
    {
        // check with empty string
        $this->assertFalse($cookie->has(''), 'The cookie doesn\'t exist');

        // check with string
        $this->assertFalse($cookie->has('test'), 'The cookie doesn\'t exist');

        // check with null
        try {
            $this->assertFalse($cookie->has(null), 'The cookie doesn\'t exist');
            $this->fail('Parameter must be a string');
        } catch (\FMUP\Exception $e) {
            $this->assertEquals('Parameter must be a string', $e->getMessage());
        }

        // check with boolean
        try {
            $this->assertFalse($cookie->has(true), 'The cookie doesn\'t exist');
            $this->fail('Parameter must be a string');
        } catch (\FMUP\Exception $e) {
            $this->assertEquals('Parameter must be a string', $e->getMessage());
        }

        // check with object
        try {
            $this->assertFalse($cookie->has(new \stdClass()), 'The cookie doesn\'t exist');
            $this->fail('Parameter must be a string');
        } catch (\FMUP\Exception $e) {
            $this->assertEquals('Parameter must be a string', $e->getMessage());
        }
        return $cookie;
    }

    public function testSet()
    {
        $cookie = $this->getCookieMock();
        $cookie->expects($this->exactly(3))->method('setCookie');
        $cookie->expects($this->at(1))->method('setCookie')->with(
            $this->equalTo('cookie1'),
            $this->equalTo('cookie1'),
            $this->greaterThanOrEqual(time() + 10)
        );


        /** @var Cookie $cookie */
        $cookieName = 'cookie0';
        $cookieValue = 'cookie0';
        $return = $cookie->set($cookieName, $cookieValue);
        $this->assertSame($cookie, $return);

        $cookieName = 14563;
        $cookieValue = 14563;
        try {
            $return = $cookie->set($cookieName, $cookieValue);
            $this->fail('Cannot set cookie with numeric Id');
        } catch (\FMUP\Exception $e) {
            $this->assertEquals('Parameter must be a string', $e->getMessage());
        }
        $this->assertSame($return, $cookie);

        $cookieName = 'cookie1';
        $cookieValue = 'cookie1';
        $cookieExpire = 10;
        $return = $cookie->set($cookieName, $cookieValue, $cookieExpire);
        $this->assertSame($return, $cookie);

        $cookieName = 'cookie2';
        $cookieValue = 'cookie2';
        $expire = time() + 24 * 60 * 60;
        $return = $cookie->set($cookieName, $cookieValue, $expire);
        $this->assertSame($return, $cookie);

        return $cookie;
    }

    /**
     * test method get
     * @depends testHas
     */
    public function testGet(Cookie $cookie)
    {
        $this->assertEquals(null, $cookie->get('test'));
        try {
            $cookie->get(10);
            $this->fail();
        } catch (\FMUP\Exception $e) {
            $this->assertSame('Parameter must be a string', $e->getMessage());
        }
        $_COOKIE['bob'] = '12';
        $this->assertEquals($_COOKIE['bob'], $cookie->get('bob'));
        return $cookie;
    }

    /**
     * test method remove
     */
    public function testRemove()
    {
        $cookie = $this->getCookieMock();
        $cookie->expects($this->exactly(1))->method('setCookie');
        /** @var $cookie Cookie */
        $this->assertSame($cookie, $cookie->remove('bleu'));
        $_COOKIE['bob'] = '12';
        $this->assertSame($cookie, $cookie->remove('bob'));
        $this->assertFalse(isset($_COOKIE['bob']));
        try {
            $cookie->remove(12);
            $this->fail('Must not be able to remove a non string');
        } catch (\FMUP\Exception $e) {
            $this->assertSame('Parameter must be a string', $e->getMessage());
        }
        return $cookie;
    }

    public function testDestroy()
    {
        $cookie = $this->getCookieMock();
        $_COOKIE = array('test1' => 'test1', 'hello' => 1);
        $cookie->expects($this->exactly(2))->method('setCookie');
        /** @var $cookie Cookie */
        $cookie->destroy();
        $this->assertSame(array(), $_COOKIE);
    }
}
