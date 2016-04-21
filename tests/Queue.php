<?php
namespace Tests;

class QueueTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $queue = new \FMUP\Queue('bob');
        $this->assertInstanceOf(\FMUP\Queue::class, $queue, 'Queue is not instance of ' . \FMUP\Queue::class);
        $queue2 = new \FMUP\Queue('bob2');
        $this->assertInstanceOf(\FMUP\Queue::class, $queue2, 'Queue2 is not instance of ' . \FMUP\Queue::class);
        $this->assertNotSame($queue2, $queue, 'Queue2 is same than queue1');
        $this->assertNotEquals($queue2, $queue, 'Queue2 is same than queue1');
        $queue3 = new \FMUP\Queue('bob3');
        $this->assertInstanceOf(\FMUP\Queue::class, $queue3, 'Queue3 is not instance of ' . \FMUP\Queue::class);
        $this->assertNotSame($queue3, $queue, 'Queue3 is same than queue1');
        $this->assertNotEquals($queue3, $queue, 'Queue3 is same than queue1');
        $this->assertNotSame($queue3, $queue2, 'Queue3 is same than queue2');
        $this->assertNotEquals($queue3, $queue2, 'Queue3 is same than queue2');
        return $queue;
    }

    /**
     * @depends testConstruct
     * @param \FMUP\Queue $queue
     */
    public function testGetOrDefineChannel(\FMUP\Queue $queue)
    {
        $channel = $queue->getOrDefineChannel();
        $this->assertInstanceOf(\FMUP\Queue\Channel::class, $channel, 'Unable to create channel');
        $channel2 = $queue->getOrDefineChannel();
        $this->assertSame($channel2, $channel, 'Channel seems created twice, must optimize');
        $queue = new \FMUP\Queue('');
        try {
            $queue->getOrDefineChannel();
            $this->assertTrue(false, 'Unable to generate error with empty channel name');
        } catch (\FMUP\Queue\Exception $e) {
            $this->assertTrue(true, 'Error while trying to generate exception on empty channel name');
        }
        $this->assertEquals('bob', $channel->getName(), 'Error while asserting channel creation uses name');
    }

    /**
     * @depends testConstruct
     * @param \FMUP\Queue $queue
     * @throws \FMUP\Queue\Exception
     */
    public function testSetChannel(\FMUP\Queue $queue)
    {
        $channel = new \FMUP\Queue\Channel('unit');
        $return = $queue->setChannel($channel);
        $this->assertSame($return, $queue, 'Fluent interface broken');
        $this->assertSame($channel, $queue->getOrDefineChannel(), 'Set channel did not work');
    }

    /**
     * @depends testConstruct
     * @param \FMUP\Queue $queue
     * @return \FMUP\Queue
     */
    public function testGetDriver(\FMUP\Queue $queue)
    {
        $queue2 = clone $queue;
        $config = $this->getMock(\FMUP\Config::class);
        $config->method('has')->willReturn(true);
        $config->method('get')->willReturn('Unit');
        $environment = \FMUP\Environment::getInstance()->setConfig($config);
        $queue->setEnvironment($environment);
        $driver = $queue->getDriver();
        $this->assertInstanceOf(\FMUP\Queue\DriverInterface::class, $driver, 'Get Driver instance must return an instance of DriverInterface');
        $this->assertInstanceOf(\FMUP\Queue\Driver\Native::class, $driver, 'Default behaviour is to define a driver Native');
        if ($driver instanceof \FMUP\Environment\OptionalInterface) {
            $this->assertTrue($driver->hasEnvironment());
            $this->assertSame($environment, $driver->getEnvironment(), 'Driver must have same environment than queue if defined');
        }

        $driver = $this->getDriverMock();
        $queue2->setDriver($driver);
        $this->assertSame($driver, $queue2->getDriver(), 'Get Driver must use set driver');
        return $queue;
    }

    /**
     * @depends testConstruct
     * @param \FMUP\Queue $queue
     * @return \FMUP\Queue
     */
    public function testSetDriver(\FMUP\Queue $queue)
    {
        $return = $queue->setDriver($this->getDriverMock());
        $this->assertEquals($queue, $return, 'Set Driver instance must return its instance');
        return $queue;
    }

    /**
     * @depends testConstruct
     * @param \FMUP\Queue $queueOriginal
     * @return \FMUP\Queue
     */
    public function testPush(\FMUP\Queue $queueOriginal)
    {
        $queue = clone $queueOriginal;
        $messagesToSend = array('test', 1, '123', -1, new \stdClass());
        $nbMessageToSend = count($messagesToSend);
        $messageSent = 0;
        $mockDriver = $this->getDriverMock();
        $mockDriver->method('push')->willReturn(true);
        $queue->setDriver($mockDriver);
        foreach ($messagesToSend as $message) {
            $return = $queue->push($message);
            $this->assertTrue($return, 'Unable to send ' . serialize($message));
            if ($return) {
                $messageSent++;
            }
        }
        $this->assertEquals($nbMessageToSend, $messageSent, 'Fail asserting message to send VS messages sent');
        $mockDriver = $this->getDriverMock();
        $mockDriver->method('push')->willReturn(false);
        $queue->setDriver($mockDriver);
        foreach ($messagesToSend as $message) {
            $this->assertFalse($queue->push($message), 'Unable to send ' . serialize($message));
        }
        return $queue;
    }

    /**
     * @depends testPush
     * @param \FMUP\Queue $queue
     * @return \FMUP\Queue
     */
    public function testPull(\FMUP\Queue $queue)
    {
        $array = array();
        $values = array(1, -2, '12', '');
        $mockDriver = $this->getDriverMock();
        foreach ($values as $index => $value) {
            $mockDriver->expects($this->at($index))->method('pull')->willReturn($value);
        }
        $queue->setDriver($mockDriver);
        while ($array[] = $queue->pull()) ;
        foreach ($array as $key => $msgReceived) {
            $this->assertSame($values[$key], $msgReceived);
        }
        return $queue;
    }

    /**
     * @depends testConstruct
     * @param \FMUP\Queue $queueOriginal
     */
    public function testGetStats(\FMUP\Queue $queueOriginal)
    {
        $queue = clone $queueOriginal;
        $mockDriver = $this->getDriverMock();
        $mockDriver->expects($this->at(0))->method('getStats')->willReturn(array('stats' => 1));
        $mockDriver->expects($this->at(1))->method('getStats')->willThrowException(new \FMUP\Queue\Exception());
        $queue->setDriver($mockDriver);
        $stats = $queue->getStats();
        $this->assertEquals(1, $stats['stats'], 'Stats not returned');
        $this->setExpectedException(\FMUP\Queue\Exception::class);
        $queue->getStats();
    }

    /**
     * @depends testConstruct
     * @param \FMUP\Queue $queueOriginal
     * @return \FMUP\Queue
     */
    public function testAckMessage(\FMUP\Queue $queueOriginal)
    {
        $queue = clone $queueOriginal;
        $mockDriver = $this->getDriverMock();
        $mockDriver->expects($this->atLeast(1))->method('ackMessage')->willReturn($mockDriver);
        $queue->setDriver($mockDriver);
        $return = $queue->ackMessage($this->getMock(\FMUP\Queue\Message::class));
        $this->assertSame($mockDriver, $return);
        return $queueOriginal;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getDriverMock()
    {
        return $this->getMock(\FMUP\Queue\DriverInterface::class);
    }

}
