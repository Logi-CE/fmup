<?php
/**
 * Cli.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Request;


class CliTest extends \PHPUnit_Framework_TestCase
{
    public function testHas()
    {
        $requestCli = $this->getMockBuilder(\FMUP\Request\Cli::class)->setMethods(array('getOpt'))->getMock();
        $requestCli->method('getOpt')
            ->will(
                $this->onConsecutiveCalls(
                    array(),
                    array('v' => 1),
                    array('verbose' => 'value')
                )
            );
        /** @var $requestCli \FMUP\Request\Cli */
        $this->assertFalse($requestCli->has('v'));
        $this->assertTrue($requestCli->has('v'));
        $this->assertFalse($requestCli->has('v'));
    }

    public function testGet()
    {
        $requestCli = $this->getMockBuilder(\FMUP\Request\Cli::class)->setMethods(array('getOpt'))->getMock();
        $requestCli->method('getOpt')
            ->will(
                $this->onConsecutiveCalls(
                    array(),
                    array('v' => 1),
                    array('verbose' => 'value')
                )
            );
        /** @var $requestCli \FMUP\Request\Cli */
        $this->assertNull($requestCli->get('v'));
        $this->assertSame(1, $requestCli->get('v'));
        $this->assertSame('test', $requestCli->get('v', 'test'));
    }

    public function testDefineOptAndGet()
    {
        $requestCli = $this->getMockBuilder(\FMUP\Request\Cli::class)->setMethods(array('phpGetOpt'))->getMock();
        $requestCli->expects($this->exactly(1))
            ->method('phpGetOpt')
            ->with($this->equalTo('helo'), $this->equalTo(array('test:', 'route:')));
        /** @var $requestCli \FMUP\Request\Cli */
        $this->assertSame(
            array(\FMUP\Request\Cli::SHORT => '', \FMUP\Request\Cli::LONG => array('route:')),
            $requestCli->getDefinedOpt()
        );
        $this->assertSame($requestCli, $requestCli->defineOpt('mal'));
        $this->assertSame(
            array(\FMUP\Request\Cli::SHORT => 'mal', \FMUP\Request\Cli::LONG => array('route:')),
            $requestCli->getDefinedOpt()
        );
        $this->assertSame($requestCli, $requestCli->defineOpt('helo', array('test:')));
        $this->assertSame(
            array(\FMUP\Request\Cli::SHORT => 'helo', \FMUP\Request\Cli::LONG => array('test:', 'route:')),
            $requestCli->getDefinedOpt()
        );
        $requestCli->getOpt();
    }

    public function testGetRequestUri()
    {
        $requestCli = $this->getMockBuilder(\FMUP\Request\Cli::class)->setMethods(array('get'))->getMock();
        $requestCli->method('get')->will($this->onConsecutiveCalls('my/route', 'another/route'));
        /** @var $requestCli \FMUP\Request\Cli */
        $this->assertSame('my/route', $requestCli->getRequestUri());
        $_SERVER['argc'] = 3;
        $_SERVER['argv'] = array('test.php', '-v', '--param2', 10);
        $this->assertSame('-v --param2 10', $requestCli->getRequestUri(true));
    }
}
