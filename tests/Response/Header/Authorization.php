<?php
/**
 * Authorization.php
 * @author: jyamin@castelis.com
 */
namespace Tests\Response\Header;

use FMUP\Response\Header\Authorization;

class AuthorizationTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $authorization = new Authorization();
        $this->assertInstanceOf('\FMUP\Response\Header', $authorization);
        $this->assertSame('Bearer', $authorization->getAuthorizationType());
        $this->assertNull($authorization->getToken());

        $authorization = new Authorization('otherType', 'token');
        $this->assertInstanceOf('\FMUP\Response\Header', $authorization);
        $this->assertSame('otherType', $authorization->getAuthorizationType());
        $this->assertSame('token', $authorization->getToken());
    }

    public function testGetSetAuthorizationType()
    {
        $authorization = new Authorization();
        $this->assertSame('Bearer', $authorization->getAuthorizationType());
        $this->assertSame($authorization, $authorization->setAuthorizationType('Test'));
        $this->assertSame('Test', $authorization->getAuthorizationType());
    }

    public function testGetSetToken()
    {
        $authorization = new Authorization();
        $this->assertNull($authorization->getToken());
        $this->assertSame($authorization, $authorization->setToken('tokentest'));
        $this->assertSame('tokentest', $authorization->getToken());
    }

    public function testGetValue()
    {
        $authorization = new Authorization();
        $this->assertSame('Bearer ', $authorization->getValue());

        $authorization->setToken('tokenTest');
        $this->assertSame('Bearer tokenTest', $authorization->getValue());
    }

    public function testGetType()
    {
        $authorization = new Authorization();
        $this->assertSame(Authorization::TYPE, $authorization->getType());
    }
}
