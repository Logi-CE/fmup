<?php
/**
 * ByMask.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Routing;

class MatchingRouteMock extends \FMUP\Routing\Route
{
    public function canHandle()
    {
        // TODO: Implement canHandle() method.
    }

    public function getAction()
    {
        // TODO: Implement getAction() method.
    }

    public function getControllerName()
    {
        // TODO: Implement getControllerName() method.
    }
}

class ByMaskTest extends \PHPUnit_Framework_TestCase
{
    public function testDispatchWithoutMask()
    {
        $request = $this->getMock(\FMUP\Request\Cli::class, array('getRequestUri'));
        $request->method('getRequestUri')->willReturn('/');
        $byMask = $this->getMock(\FMUP\Routing\ByMask::class, array('getMasks', 'addRoute'));
        $byMask->method('getMasks')->willReturn(array());
        $byMask->expects($this->never())->method('addRoute');
        /**
         * @var $byMask \FMUP\Routing\ByMask
         * @var $request \FMUP\Request
         */
        $this->assertInstanceOf(\FMUP\Routing::class, $byMask);
        $this->assertNull($byMask->dispatch($request));
    }

    public function testDispatchWithoutMatchingMask()
    {
        $request = $this->getMock(\FMUP\Request\Cli::class, array('getRequestUri'));
        $request->method('getRequestUri')->willReturn('/not/matching');
        $byMask = $this->getMock(\FMUP\Routing\ByMask::class, array('getMasks', 'addRoute'));
        $byMask->expects($this->never())->method('addRoute');
        $byMask->method('getMasks')->willReturn(
            array(
                '^/$' => \stdClass::class,
                '^/not/$' => \stdClass::class,
            )
        );
        /**
         * @var $byMask \FMUP\Routing\ByMask
         * @var $request \FMUP\Request
         */
        $this->assertInstanceOf(\FMUP\Routing::class, $byMask);
        $this->assertNull($byMask->dispatch($request));
    }

    public function testDispatchWithNonValidMatchingMask()
    {
        $request = $this->getMock(\FMUP\Request\Cli::class, array('getRequestUri'));
        $request->method('getRequestUri')->willReturn('/not/matching');
        $byMask = $this->getMock(\FMUP\Routing\ByMask::class, array('getMasks', 'addRoute'));
        $byMask->expects($this->never())->method('addRoute');
        $byMask->method('getMasks')->willReturn(
            array(
                '^/$' => \stdClass::class,
                '^/not/matching$' => \stdClass::class,
            )
        );
        /**
         * @var $byMask \FMUP\Routing\ByMask
         * @var $request \FMUP\Request
         */
        $this->assertInstanceOf(\FMUP\Routing::class, $byMask);
        $this->assertNull($byMask->dispatch($request));
    }

    public function testDispatchWithValidMatchingMask()
    {
        $request = $this->getMock(\FMUP\Request\Cli::class, array('getRequestUri'));
        $request->method('getRequestUri')->willReturn('/will/match');
        $byMask = $this->getMock(\FMUP\Routing\ByMask::class, array('getMasks', 'addRoute'));
        $byMask->expects($this->once())->method('addRoute');
        $byMask->method('getMasks')->willReturn(
            array(
                '^/$' => \stdClass::class,
                '^/will/match$' => MatchingRouteMock::class,
                '^/will/match(/too)?$' => MatchingRouteMock::class,
            )
        );
        /**
         * @var $byMask \FMUP\Routing\ByMask
         * @var $request \FMUP\Request
         */
        $this->assertInstanceOf(\FMUP\Routing::class, $byMask);
        $byMask->dispatch($request);
    }
}
