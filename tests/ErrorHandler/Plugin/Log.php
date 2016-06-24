<?php
/**
 * Log.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\ErrorHandler\Plugin;


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
        $log = $this->getMockBuilder(\FMUP\ErrorHandler\Plugin\Log::class)->setMethods(array('errorLog'))->getMock();
        $log->expects($this->exactly(1))->method('errorLog')->with($this->equalTo('unit test message'));
        /** @var $log \FMUP\ErrorHandler\Plugin\Log */
        $this->assertSame($log, $log->setException(new \Exception('unit test message'))->handle());
    }
}
