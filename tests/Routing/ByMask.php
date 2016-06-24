<?php
/**
 * ByMask.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests\Routing;

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
        $request = $this->getMockBuilder(\FMUP\Request\Cli::class)->setMethods(array('getRequestUri'))->getMock();
        $request->method('getRequestUri')->willReturn('/');
        $byMask = $this->getMockBuilder(\FMUP\Routing\ByMask::class)->setMethods(array('getMasks', 'addRoute'))->getMock();
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
        $request = $this->getMockBuilder(\FMUP\Request\Cli::class)->setMethods(array('getRequestUri'))->getMock();
        $request->method('getRequestUri')->willReturn('/not/matching');
        $byMask = $this->getMockBuilder(\FMUP\Routing\ByMask::class)->setMethods(array('getMasks', 'addRoute'))->getMock();
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
        $request = $this->getMockBuilder(\FMUP\Request\Cli::class)->setMethods(array('getRequestUri'))->getMock();
        $request->method('getRequestUri')->willReturn('/not/matching');
        $byMask = $this->getMockBuilder(\FMUP\Routing\ByMask::class)->setMethods(array('getMasks', 'addRoute'))->getMock();
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
        $request = $this->getMockBuilder(\FMUP\Request\Cli::class)->setMethods(array('getRequestUri'))->getMock();
        $request->method('getRequestUri')->willReturn('/will/match');
        $byMask = $this->getMockBuilder(\FMUP\Routing\ByMask::class)->setMethods(array('getMasks', 'addRoute'))->getMock();
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
