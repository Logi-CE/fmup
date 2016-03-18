<?php

namespace Tests;

use FMUP\Cookie;

class CookieTest extends \PHPUnit_Framework_TestCase
{
    const WRONG_EXCEPTION_CODE = 'Wrong exception code.';
    const ERROR_NOT_INSTANCE_OF = 'Not an instance of %s';

    public function testGetInstance()
    {
        $cookie = Cookie::getInstance();
        $this->assertInstanceOf('\FMUP\Cookie', $cookie, sprintf(self::ERROR_NOT_INSTANCE_OF, 'FMUP\Cookie'));
        $cookie2 = Cookie::getInstance();
        $this->assertSame($cookie, $cookie2, sprintf(self::ERROR_NOT_INSTANCE_OF, 'FMUP\Cookie'));
        return $cookie;
    }

    /**
     * @depends testGetInstance
     * @param Cookie $cookie
     * @return Cookie
     */
    public function testHas($cookie)
    {
        // check with empty string
        $this->assertFalse($cookie->has(''), 'The cookie doesn\'t exist');

        // check with string
        $this->assertFalse($cookie->has('test'), 'The cookie doesn\'t exist');

        // check with null
        $this->assertFalse($cookie->has(null), 'The cookie doesn\'t exist');

        // check with boolean
        $this->assertFalse($cookie->has(true), 'The cookie doesn\'t exist');

        // check with object
        try{
            $this->assertFalse($cookie->has(new \stdClass()), 'The cookie doesn\'t exist');
        } catch (\Exception $e) {
            $this->assertEquals('2', $e->getCode(), self::WRONG_EXCEPTION_CODE);
        }

        return $cookie;
    }

    /**
     * @depends testGetInstance
     * @param Cookie $cookie
     * @return Cookie
     */
    public function testSetGet($cookie)
    {
        $this->markTestSkipped('Test skipped because the error \'Cannot modify header information - headers already sent\'');

        $cookieName = 'cookie1';
        $cookieValue = 'value1';

        $cookie->set($cookieName, $cookieValue);
        $this->assertEquals($cookieValue, $cookie->get($cookieName), 'The value returned is not the expected value for the cookie');

        $cookieName = 'cookie2';
        $cookieValue = 'value2';
        $cookie->set($cookieName, $cookieValue, 200);
        $this->assertEquals($cookieValue, $cookie->get($cookieName), 'The value returned is not the expected value for the cookie');

        $cookieName = 'cookie3';
        $cookieValue = 'value3';
        $cookie->set($cookieName, $cookieValue, 200, 'tests/');
        $this->assertEquals($cookieValue, $cookie->get($cookieName), 'The value returned is not the expected value for the cookie');

        $cookieName = 'cookie4';
        $cookieValue = 'value4';
        $cookie->set($cookieName, $cookieValue, 200, 'tests/', 'unitTests');
        $this->assertEquals($cookieValue, $cookie->get($cookieName), 'The value returned is not the expected value for the cookie');

        $cookieName = 'cookie5';
        $cookieValue = 'value5';
        $cookie->set($cookieName, $cookieValue, 200, 'tests/', 'unitTests', true);
        $this->assertEquals($cookieValue, $cookie->get($cookieName), 'The value returned is not the expected value for the cookie');

        $cookieName = 'cookie6';
        $cookieValue = 'value6';
        $cookie->set($cookieName, $cookieValue, 200, 'tests/', 'unitTests', true, true);
        $this->assertEquals($cookieValue, $cookie->get($cookieName), 'The value returned is not the expected value for the cookie');

        return $cookie;
    }

    /**
     * @depends testGetInstance
     * @param Cookie $cookie
     * @return Cookie
     */
    public function testGet($cookie)
    {
        $this->markTestSkipped('Test skipped because not written');
    }

    /**
     * @depends testGetInstance
     * @param Cookie $cookie
     * @return Cookie
     */
    public function testRemove($cookie)
    {
        $this->markTestSkipped('Test skipped because not written');
    }

    /**
     * @depends testGetInstance
     * @param Cookie $cookie
     * @return Cookie
     */
    public function testDestroy($cookie)
    {
        $this->markTestSkipped('Test skipped because not written');
    }
}
