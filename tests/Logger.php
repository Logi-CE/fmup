<?php
/**
 * Logger.php
 * @author: jmoulin@castelis.com
 */
namespace Tests\Logger;

class FactoryMockLogger extends \FMUP\Logger\Factory
{
    public function __construct()
    {

    }
}


namespace Tests;

class LoggerTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGetFactory()
    {
        $logger = new \FMUP\Logger();
        $factory = $logger->getFactory();
        $this->assertInstanceOf(\FMUP\Logger\Factory::class, $factory);

        $logger2 = new \FMUP\Logger();
        $factoryMock = $this->getMock(\Tests\Logger\FactoryMockLogger::class);

        $reflection = new \ReflectionProperty(\FMUP\Logger\Factory::class, 'instance');
        $reflection->setAccessible(true);
        $reflection->setValue(\FMUP\Logger\Factory::getInstance(), $factoryMock);

        /** @var $factoryMock \Tests\Logger\FactoryMockLogger */
        $logger->setFactory($factoryMock);
        $this->assertInstanceOf(\Tests\Logger\FactoryMockLogger::class, $logger2->getFactory());
    }

    public function testGetRequestWhenNotSet()
    {
        $this->setExpectedException(\LogicException::class, 'Request is not defined');
        (new \FMUP\Logger)->getRequest();
    }

    public function testSetGetRequest()
    {
        $logger = new \FMUP\Logger;
        $request = $this->getMock(\FMUP\Request::class, array('get', 'set', 'has', 'getRequestUri'));
        /** @var $request \FMUP\Request */
        $logger->setRequest($request);
        $this->assertSame($request, $logger->getRequest());
    }

    public function testGetEnvironment()
    {
        $logger = new \FMUP\Logger;
        $this->assertInstanceOf(\FMUP\Environment::class, $logger->getEnvironment());
    }

    public function testSetGet()
    {
        $logger = new \FMUP\Logger;
        $loggerChannel = $logger->get('standard');
        $this->assertInstanceOf(\FMUP\Logger\Channel::class, $loggerChannel);
        $loggerChannel2 = $logger->get('standard');
        $this->assertSame($loggerChannel, $loggerChannel2);

        $fakeChannel = $this->getMock(\FMUP\Logger\Channel::class, array('getName', 'configure'));
        /** @var $fakeChannel \FMUP\Logger\Channel */
        $logger->set($fakeChannel, 'fakeChannel');
        $this->assertSame($fakeChannel, $logger->get('fakeChannel'));
    }

    public function testLog()
    {
        $fakeChannel = $this->getMock(\FMUP\Logger\Channel::class, array('addRecord', 'getName', 'configure'));
        $fakeChannel2 = $this->getMock(\FMUP\Logger\Channel::class, array('addRecord', 'getName', 'configure'));
        $fakeChannel2->method('getName')->willReturn(\FMUP\Logger\Channel\Standard::NAME);
        $fakeChannel->expects($this->at(1))
            ->method('addRecord')
            ->with($this->equalTo(\FMUP\Logger::ALERT), $this->equalTo('test unit 1'), $this->equalTo(array()));
        $fakeChannel2->expects($this->at(1))
            ->method('addRecord')
            ->with(
                $this->equalTo(\FMUP\Logger::ERROR),
                $this->equalTo('[Channel fakeChannel] test unit 2'),
                $this->equalTo(array('context' => 'context'))
            );
        /**
         * @var $fakeChannel \FMUP\Logger\Channel
         * @var $fakeChannel2 \FMUP\Logger\Channel
         */
        $logger = new \FMUP\Logger;
        $logger->set($fakeChannel, 'fakeChannel');
        $logger->log('fakeChannel', \FMUP\Logger::ALERT, 'test unit 1');
        $logger->set($fakeChannel2, 'fakeChannel');
        $logger->log('fakeChannel', \FMUP\Logger::ERROR, 'test unit 2', array('context' => 'context'));
    }
}
