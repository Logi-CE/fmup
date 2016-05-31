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
        $loggerMock = $this->getMock(\FMUP\Logger::class);
        $logger = $this->getMock(LoggerMockLogger::class, null);
        /** @var $logger LoggerMockLogger */
        /** @var $loggerMock \FMUP\Logger */
        $this->assertFalse($logger->hasLogger());
        $this->assertSame($logger, $logger->setLogger($loggerMock));
        $this->assertTrue($logger->hasLogger());
        $this->assertSame($loggerMock, $logger->getLogger());
    }

    public function testLog()
    {
        $loggerMock = $this->getMock(\FMUP\Logger::class, array('log'));
        $loggerMock->expects($this->once())->method('log')
            ->with(
                $this->equalTo(\FMUP\Logger\Channel\Standard::NAME),
                $this->equalTo(\FMUP\Logger::DEBUG),
                $this->equalTo('PHPunit Test Case'),
                $this->arrayHasKey('test')
            );
        $logger = $this->getMock(LoggerMockLogger::class, null);
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
        $this->expectException(\FMUP\Logger\Exception::class);
        $this->expectExceptionMessage('Logger must be defined');
        $logger->getLogger();
    }
}
