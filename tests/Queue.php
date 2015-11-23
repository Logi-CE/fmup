<?php
/**
 * Created by PhpStorm.
 * User: vcorre
 * Date: 23/11/2015
 * Time: 10:08
 */

namespace Tests;



class Queue extends \PHPUnit_Framework_TestCase
{
    private $driverMock;

    public function testConstruct()
    {
        $queue = new \FMUP\Queue('bob');
        return $queue;
    }

    /**
     * @depends testConstruct
     * @param \FMUP\Queue $queue
     * @return \FMUP\Queue
     */
    public function testGetDriver(\FMUP\Queue $queue)
    {
        $return = $queue->getDriver();
        $this->assertInstanceOf('\FMUP\Queue\DriverInterface', $return, 'Get Driver instance must return an instance of DriverInterface');
        return $queue;
    }

    /**
     * @depends testConstruct
     * @param \FMUP\Queue $queue
     * @return \FMUP\Queue
     */
    public function testSetDriver(\FMUP\Queue $queue) {
        $return = $queue->setDriver($this->getDriverMock());
        $this->assertEquals($queue, $return, 'Set Driver instance must return its instance');
        return $queue;
    }

    /**
     * @depends testConstruct
     * @param \FMUP\Queue $queue
     */
    public function testGetQueueResource(\FMUP\Queue $queue) {
        try {
            $queue->getDriver()->connect('bob');
        } catch (\FMUP\Queue\Exception $e) {
            $this->assertTrue(false, 'Queue name muse be in INT > 0 to use semaphores');
        }
    }

    /**
     * @depends testPush
     * @param \FMUP\Queue $queue
     * @return mixed|null $message
     */
    public function testPull(\FMUP\Queue $queue){
        try {
            $message = $queue->pull();
            $this->assertSame($message, 'bob' || null , 'This function should not return something that didn\'t exists');
            return $message;
        } catch (\FMUP\Queue\Exception $e) {
            $this->assertTrue(false, 'Queue name muse be in INT > 0 to use semaphores');
        }
    }

    /**
     *@depends testGetQueueResource
     */
    public function testPush(\FMUP\Queue $queue){
        try {
            $queue->push('bob', null);

        } catch (\FMUP\Queue\Exception $e) {
            $this->assertTrue(false, 'Queue name muse be in INT > 0 to use semaphores');
        }
        return $queue;
    }

    public function testGetStats() {

    }

    /**
     * @return \FMUP\Queue\Driver\Native
     */
    private function getDriverMock()
    {
        if (!$this->driverMock) {
            $this->driverMock = new \FMUP\Queue\Driver\Native();
        }
        return $this->driverMock;
    }

}