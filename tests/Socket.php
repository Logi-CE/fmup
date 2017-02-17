<?php
/**
 * @author jyamin
 */

namespace Tests;

use FMUP\Socket;

class SocketTest extends \PHPUnit_Framework_TestCase
{
    public function testConnect()
    {
        $socket = $this->getMockBuilder(Socket::class)
            ->setMethods(array(
                'phpFSockOpen',
            ))->getMock();
        $h = fopen('php://stdin', 'r');
        $socket->expects($this->once())
            ->method('phpFSockOpen')
            ->with('127.0.0.1', 80, null, null)
            ->willReturn($h);

        $property = new \ReflectionProperty(Socket::class, 'socket');
        $property->setAccessible(true);

        /** @var Socket $socket */
        $this->assertFalse($socket->isConnected());
        $this->assertSame($socket, $socket->connect('127.0.0.1', 80));
        $this->assertSame($h, $property->getValue($socket));
        $this->assertTrue($socket->isConnected());
        $this->assertNull($socket->getErrorNumber());
        $this->assertNull($socket->getErrorString());
    }

    public function testWrite()
    {
        $socket = $this->getMockBuilder(Socket::class)
            ->setMethods(array('phpFWrite'))
            ->getMock();

        $property = new \ReflectionProperty(Socket::class, 'socket');
        $property->setAccessible(true);
        $h = fopen('php://stdin', 'r');
        $property->setValue($socket, $h);

        $socket->expects($this->exactly(2))
            ->method('phpFWrite')
            ->withConsecutive(array($h, 'Hello world', 5), array($h, 'Hello world'))
            ->willReturnOnConsecutiveCalls(5, 11);

        /** @var Socket $socket */
        $this->assertSame(5, $socket->write('Hello world', 5));
        $this->assertSame(11, $socket->write('Hello world'));
    }

    public function testRead()
    {
        $socket = $this->getMockBuilder(Socket::class)
            ->setMethods(array('phpFRead'))
            ->getMock();

        $h = fopen('php://stdin', 'r');
        $proterty = new \ReflectionProperty(Socket::class, 'socket');
        $proterty->setAccessible(true);
        $proterty->setValue($socket, $h);

        $socket->expects($this->once())
            ->method('phpFRead')
            ->with($h, 4)
            ->willReturn('line');

        /** @var Socket $socket */
        $this->assertSame('line', $socket->read(4));
    }

    public function testGetErr()
    {
        $socket = new Socket();

        $errno = new \ReflectionProperty(Socket::class, 'errorNumber');
        $errno->setAccessible(true);
        $errno->setValue($socket, 1);
        $errstr = new \ReflectionProperty(Socket::class, 'errorString');
        $errstr->setAccessible(true);
        $errstr->setValue($socket, 'Error');

        $this->assertSame(1, $socket->getErrorNumber());
        $this->assertSame('Error', $socket->getErrorString());
    }
}
