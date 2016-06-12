<?php
/**
 * LoggerTrait.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Logger;

class LoggerMockLogger implements \FMUP\Logger\LoggerInterface
{
    use \FMUP\Logger\LoggerTrait;
}

class LoggerTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGetHasLogger()
    {
        $loggerMock = $this->getMockBuilder('\FMUP\Logger')->getMock();
        $logger = $this->getMockBuilder('\Tests\Logger\LoggerMockLogger')->setMethods(null)->getMock();
        /** @var $logger LoggerMockLogger */
        /** @var $loggerMock \FMUP\Logger */
        $this->assertFalse($logger->hasLogger());
        $this->assertSame($logger, $logger->setLogger($loggerMock));
        $this->assertTrue($logger->hasLogger());
        $this->assertSame($loggerMock, $logger->getLogger());
    }

    public function testLog()
    {
        $loggerMock = $this->getMockBuilder('\FMUP\Logger')->setMethods(array('log'))->getMock();
        $loggerMock->expects($this->once())->method('log')
            ->with(
                $this->equalTo(\FMUP\Logger\Channel\Standard::NAME),
                $this->equalTo(\FMUP\Logger::DEBUG),
                $this->equalTo('PHPunit Test Case'),
                $this->arrayHasKey('test')
            );
        $logger = $this->getMockBuilder('\Tests\Logger\LoggerMockLogger')->setMethods(null)->getMock();
        /** @var $logger LoggerMockLogger */
        /** @var $loggerMock \FMUP\Logger */
        $this->assertFalse($logger->hasLogger());
        $this->assertSame($logger, $logger->log(\FMUP\Logger::DEBUG, 'PHPunit Test Case', array()));
        $this->assertSame($logger, $logger->setLogger($loggerMock));
        $this->assertTrue($logger->hasLogger());
        $this->assertSame($logger, $logger->log(\FMUP\Logger::DEBUG, 'PHPunit Test Case', array('test' => 'test')));
    }

    public function testGetLoggerFailWhenNotSet()
    {
        $logger = new LoggerMockLogger;
        $this->setExpectedException('\FMUP\Logger\Exception', 'Logger must be defined');
        $logger->getLogger();
    }
}
