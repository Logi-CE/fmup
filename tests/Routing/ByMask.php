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
        $request = $this->getMockBuilder('\FMUP\Request\Cli')->setMethods(array('getRequestUri'))->getMock();
        $request->method('getRequestUri')->willReturn('/');
        $byMask = $this->getMockBuilder('\FMUP\Routing\ByMask')->setMethods(array('getMasks', 'addRoute'))->getMock();
        $byMask->method('getMasks')->willReturn(array());
        $byMask->expects($this->never())->method('addRoute');
        /**
         * @var $byMask \FMUP\Routing\ByMask
         * @var $request \FMUP\Request
         */
        $this->assertInstanceOf('\FMUP\Routing', $byMask);
        $this->assertNull($byMask->dispatch($request));
    }

    public function testDispatchWithoutMatchingMask()
    {
        $request = $this->getMockBuilder('\FMUP\Request\Cli')->setMethods(array('getRequestUri'))->getMock();
        $request->method('getRequestUri')->willReturn('/not/matching');
        $byMask = $this->getMockBuilder('\FMUP\Routing\ByMask')->setMethods(array('getMasks', 'addRoute'))->getMock();
        $byMask->expects($this->never())->method('addRoute');
        $byMask->method('getMasks')->willReturn(
            array(
                '^/$' => '\stdClass',
                '^/not/$' => '\stdClass',
            )
        );
        /**
         * @var $byMask \FMUP\Routing\ByMask
         * @var $request \FMUP\Request
         */
        $this->assertInstanceOf('\FMUP\Routing', $byMask);
        $this->assertNull($byMask->dispatch($request));
    }

    public function testDispatchWithNonValidMatchingMask()
    {
        $request = $this->getMockBuilder('\FMUP\Request\Cli')->setMethods(array('getRequestUri'))->getMock();
        $request->method('getRequestUri')->willReturn('/not/matching');
        $byMask = $this->getMockBuilder('\FMUP\Routing\ByMask')->setMethods(array('getMasks', 'addRoute'))->getMock();
        $byMask->expects($this->never())->method('addRoute');
        $byMask->method('getMasks')->willReturn(
            array(
                '^/$' => '\stdClass',
                '^/not/matching$' => '\stdClass',
            )
        );
        /**
         * @var $byMask \FMUP\Routing\ByMask
         * @var $request \FMUP\Request
         */
        $this->assertInstanceOf('\FMUP\Routing', $byMask);
        $this->assertNull($byMask->dispatch($request));
    }

    public function testDispatchWithValidMatchingMask()
    {
        $request = $this->getMockBuilder('\FMUP\Request\Cli')->setMethods(array('getRequestUri'))->getMock();
        $request->method('getRequestUri')->willReturn('/will/match');
        $byMask = $this->getMockBuilder('\FMUP\Routing\ByMask')->setMethods(array('getMasks', 'addRoute'))->getMock();
        $byMask->expects($this->once())->method('addRoute');
        $byMask->method('getMasks')->willReturn(
            array(
                '^/$' => '\stdClass',
                '^/will/match$' => '\Tests\Routing\MatchingRouteMock',
                '^/will/match(/too)?$' => '\Tests\Routing\MatchingRouteMock',
            )
        );
        /**
         * @var $byMask \FMUP\Routing\ByMask
         * @var $request \FMUP\Request
         */
        $this->assertInstanceOf('\FMUP\Routing', $byMask);
        $byMask->dispatch($request);
    }
}
