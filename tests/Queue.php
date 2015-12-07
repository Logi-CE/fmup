<?php
namespace Tests;

use FMUP\Queue\DriverInterface;

class QueueTest extends \PHPUnit_Framework_TestCase
{
    private $driverMock;
    private $channelMock;

    public function testConstruct()
    {
        $queue = new \FMUP\Queue('bob');
        $this->assertInstanceOf('\FMUP\Queue', $queue, 'Queue is not instance of \FMUP\Queue');
        $queue2 = new \FMUP\Queue('bob2');
        $this->assertInstanceOf('\FMUP\Queue', $queue2, 'Queue2 is not instance of \FMUP\Queue');
        $this->assertNotSame($queue2, $queue, 'Queue2 is same than queue1');
        $this->assertNotEquals($queue2, $queue, 'Queue2 is same than queue1');
        $queue3 = new \FMUP\Queue('bob3');
        $this->assertInstanceOf('\FMUP\Queue', $queue3, 'Queue3 is not instance of \FMUP\Queue');
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
        $this->assertInstanceOf('\FMUP\Queue\Channel', $channel, 'Unable to create channel');
        $channel2 = $queue->getOrDefineChannel();
        $this->assertSame($channel2, $channel, 'Channel seems created twice, must optimize');
        $queue = new \FMUP\Queue('');
        try {
            $channel3 = $queue->getOrDefineChannel();
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
        $environment = \FMUP\Environment::getInstance();
        $queue->setEnvironment($environment);
        $return = $queue->getDriver();
        $this->assertInstanceOf('\FMUP\Queue\DriverInterface', $return, 'Get Driver instance must return an instance of DriverInterface');
        $this->assertInstanceOf('\FMUP\Queue\Driver\Native', $return, 'Default behaviour is to define a driver Native');
        if ($return instanceof \FMUP\Environment\OptionalTrait) {
            $this->assertSame($environment, $return->getEnvironment(), 'Driver must have same environment than queue if defined');
        }

        $queue2->setDriver($this->getDriverMock());
        $this->assertSame($this->getDriverMock(), $queue2->getDriver(), 'Get Driver must use set driver');

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
     * @param \FMUP\Queue $queue
     * @return mixed|null $message
     */
    public function testPullPush(\FMUP\Queue $queue)
    {
        
    }

    public function testGetStats()
    {

    }

    /**
     * @return DriverInterface
     */
    private function getDriverMock()
    {
        if (!$this->driverMock) {
            $this->driverMock = $this->getMock('\FMUP\Queue\DriverInterface');
        }
        return $this->driverMock;
    }

    /**
     * @return \FMUP\Queue\Channel
     */
    private function getChannelMock()
    {
        if (!$this->channelMock) {
            $this->channelMock = $this->getMock('\FMUP\Queue\Channel');
        }
        return $this->channelMock;
    }
}
