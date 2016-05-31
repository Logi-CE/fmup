<?php
/**
 * FlashMessenger.php
 * @author: jmoulin@castelis.com
 */

namespace Tests;

class FlashMessengerMock extends \FMUP\FlashMessenger
{
    public function __construct()
    {
    }
}

class FlashMessengerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetInstance()
    {
        $reflector = new \ReflectionClass(\FMUP\FlashMessenger::class);
        $method = $reflector->getMethod('__construct');
        $this->assertTrue($method->isPrivate(), 'Construct must be private');
        try {
            $reflector->getMethod('__clone')->invoke(\FMUP\FlashMessenger::getInstance());
            $this->fail('Clone must fail');
        } catch (\ReflectionException $e) {
            $this->assertEquals(
                'Trying to invoke private method FMUP\FlashMessenger::__clone() from scope ReflectionMethod',
                $e->getMessage()
            );
        }

        $flashMessenger = \FMUP\FlashMessenger::getInstance();
        $this->assertInstanceOf(\FMUP\FlashMessenger::class, $flashMessenger);
        $flashMessenger2 = \FMUP\FlashMessenger::getInstance();
        $this->assertSame($flashMessenger, $flashMessenger2);
        return $flashMessenger;
    }

    public function testSetGetDriver()
    {
        $flashMessenger = new FlashMessengerMock();
        $driver = $flashMessenger->getDriver();
        $this->assertInstanceOf(\FMUP\FlashMessenger\DriverInterface::class, $driver);
        $this->assertSame($driver, $flashMessenger->getDriver());
        $driverMock = $this->getMock(\FMUP\FlashMessenger\DriverInterface::class, array('add', 'get', 'clear'));
        /** @var $driverMock \FMUP\FlashMessenger\DriverInterface */
        $this->assertSame($flashMessenger, $flashMessenger->setDriver($driverMock));
        $this->assertSame($driverMock, $flashMessenger->getDriver());
    }

    public function testAddGetClear()
    {
        $message1 = $this->getMock(\FMUP\FlashMessenger\Message::class, null, array('message 1'));
        $message2 = $this->getMock(\FMUP\FlashMessenger\Message::class, null, array('message 2'));
        $message3 = $this->getMock(\FMUP\FlashMessenger\Message::class, null, array('message 3'));
        $expectedMessages = array($message1, $message2, $message3);
        $flashMessenger = new FlashMessengerMock();
        $driver = $this->getMock(\FMUP\FlashMessenger\DriverInterface::class, array('add', 'get', 'clear'));
        $driver->expects($this->exactly(3))->method('add');
        $driver->expects($this->exactly(1))->method('get')->willReturn($expectedMessages);
        $driver->expects($this->exactly(2))->method('clear');
        /**
         * @var $driver \FMUP\FlashMessenger\DriverInterface
         * @var $message1 \FMUP\FlashMessenger\Message
         * @var $message2 \FMUP\FlashMessenger\Message
         * @var $message3 \FMUP\FlashMessenger\Message
         */
        $this->assertSame($flashMessenger, $flashMessenger->setDriver($driver)->add($message1));
        $flashMessenger->add($message2)->add($message3);
        $this->assertSame($expectedMessages, $flashMessenger->get());
        $flashMessenger->clear();
    }
}
