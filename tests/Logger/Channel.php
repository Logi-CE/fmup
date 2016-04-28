<?php
/**
 * Channel.php
 * @author: jmoulin@castelis.com
 */

namespace FMUP\Logger;


class ChannelTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGetLogger()
    {
        $monologLogger = $this->getMock(\Monolog\Logger::class, null, array('Mock'));
        $channel = $this->getMock(\FMUP\Logger\Channel::class, array('configure', 'getName'));
        $channel->expects($this->once())->method('getName')->willReturn('Mock');
        $channel->expects($this->once())->method('configure');
        /** @var $channel \FMUP\Logger\Channel */
        /** @var $monologLogger \Monolog\Logger */
        $defaultLogger = $channel->getLogger();
        $this->assertInstanceOf(\Monolog\Logger::class, $defaultLogger);
        $this->assertSame($channel, $channel->setLogger($monologLogger));
        $this->assertSame($monologLogger, $channel->getLogger());
        $this->assertNotSame($monologLogger, $defaultLogger);
    }

    public function testGetRequestFailWhenNotSet()
    {
        $channel = $this->getMock(\FMUP\Logger\Channel::class, array('configure', 'getName'));
        /** @var $channel \FMUP\Logger\Channel */
        $this->expectException(\FMUP\Logger\Exception::class);
        $this->expectExceptionMessage('Request must be defined');
        $channel->getRequest();
    }

    public function testGetResponseFailWhenNotSet()
    {
        $channel = $this->getMock(\FMUP\Logger\Channel::class, array('configure', 'getName'));
        /** @var $channel \FMUP\Logger\Channel */
        $this->expectException(\FMUP\Logger\Exception::class);
        $this->expectExceptionMessage('Response must be defined');
        $channel->getResponse();
    }

    public function testSetGetResponse()
    {
        $response = $this->getMock(\FMUP\Response::class);
        $channel = $this->getMock(\FMUP\Logger\Channel::class, array('configure', 'getName'));
        /** @var $channel \FMUP\Logger\Channel */
        /** @var $response \FMUP\Response */
        $this->assertSame($channel, $channel->setResponse($response));
        $this->assertSame($response, $channel->getResponse());
    }

    public function testSetGetRequest()
    {
        $request = $this->getMock(\FMUP\Request\Cli::class);
        $channel = $this->getMock(\FMUP\Logger\Channel::class, array('configure', 'getName'));
        /** @var $channel \FMUP\Logger\Channel */
        /** @var $request \FMUP\Request */
        $this->assertSame($channel, $channel->setRequest($request));
        $this->assertSame($request, $channel->getRequest());
    }

    public function testGetEnvironment()
    {
        $config = $this->getMock(\FMUP\Config::class);
        $channel = $this->getMock(\FMUP\Logger\Channel::class, array('configure', 'getName'));
        /** @var $channel \FMUP\Logger\Channel */
        $channel->setConfig($config);
        $this->assertInstanceOf(\FMUP\Environment::class, $channel->getEnvironment());
    }

    public function testAddRecord()
    {
        $monologLogger = $this->getMock(\Monolog\Logger::class, array('addRecord'), array('Mock'));
        $monologLogger->expects($this->once())->method('addRecord')
            ->with(
                $this->equalTo(\Monolog\Logger::ALERT),
                $this->equalTo('this is My Message'),
                $this->equalTo(array('context' => 'context'))
            );
        $channel = $this->getMock(\FMUP\Logger\Channel::class, array('configure', 'getName', 'getLogger'));
        $channel->expects($this->once())->method('getLogger')->willReturn($monologLogger);
        /** @var $channel \FMUP\Logger\Channel */
        $channel->addRecord(\Monolog\Logger::ALERT, 'this is My Message', array('context' => 'context'));
    }
}
