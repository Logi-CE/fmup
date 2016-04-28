<?php
/**
 * Log.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\ErrorHandler\Plugin;


class LogTest extends \PHPUnit_Framework_TestCase
{
    public function testLog()
    {
        $log = new \FMUP\ErrorHandler\Plugin\Log();
        $this->assertInstanceOf(\FMUP\ErrorHandler\Plugin\Abstraction::class, $log);
        $this->assertTrue($log->canHandle());
    }

    public function testHandle()
    {
        $log = $this->getMock(\FMUP\ErrorHandler\Plugin\Log::class, array('errorLog'));
        $log->expects($this->exactly(1))->method('errorLog')->with($this->equalTo('unit test message'));
        /** @var $log \FMUP\ErrorHandler\Plugin\Log */
        $this->assertSame($log, $log->setException(new \Exception('unit test message'))->handle());
    }
}
