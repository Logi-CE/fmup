<?php
/**
 * Route.php
 * @author: jmoulin@castelis.com
 */

namespace Tests\Routing;

class RouteTest extends \PHPUnit_Framework_TestCase
{
    public function testSetGetRequest()
    {
        $request = $this->getMockBuilder('\FMUP\Request\Cli')->getMock();
        $route = $this->getMockBuilder('\FMUP\Routing\Route')
            ->setMethods(array('canHandle', 'getControllerName', 'getAction'))
            ->getMock();
        /**
         * @var \FMUP\Routing\Route $route
         * @var \FMUP\Request $request
         */
        $this->assertSame($route, $route->setRequest($request));
        $this->assertSame($request, $route->getRequest());
    }

    public function testFailGetRequest()
    {
        $request = $this->getMockBuilder('\FMUP\Request\Cli')->getMock();
        $route = $this->getMockBuilder('\FMUP\Routing\Route')
            ->setMethods(array('canHandle', 'getControllerName', 'getAction'))
            ->getMock();
        /**
         * @var \FMUP\Routing\Route $route
         * @var \FMUP\Request $request
         */
        $this->setExpectedException('\FMUP\Exception', 'Route Request not set');
        $this->assertSame($request, $route->getRequest());
    }

    public function testHandle()
    {
        $route = $this->getMockBuilder('\FMUP\Routing\Route')
            ->setMethods(array('canHandle', 'getControllerName', 'getAction'))
            ->getMock();
        /** @var \FMUP\Routing\Route $route */
        $route->handle();
        $this->assertTrue(true);
    }

    public function testHasToBeRedispatched()
    {
        $route = $this->getMockBuilder('\FMUP\Routing\Route')
            ->setMethods(array('canHandle', 'getControllerName', 'getAction'))
            ->getMock();
        /** @var \FMUP\Routing\Route $route */
        $this->assertFalse($route->hasToBeReDispatched());
    }
}
