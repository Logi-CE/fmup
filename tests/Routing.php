<?php
/**
 * Routing.php
 * @author: jmoulin@castelis.com
 */

namespace FMUPTests;


class RoutingTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $routing = new \FMUP\Routing();
        $routing2 = clone $routing;
        $this->assertNotSame($routing, $routing2);
    }

    public function testDispatchNoRoute()
    {
        $routing = $this->getMockBuilder(\FMUP\Routing::class)->setMethods(array('defaultRoutes'))->getMock();
        $routing->expects($this->exactly(1))->method('defaultRoutes')->willReturn($routing);

        $request = $this->getMockBuilder(\FMUP\Request::class)->getMock();
        /** @var $request \FMUP\Request */
        /** @var $routing \FMUP\Routing */
        $this->assertNull($routing->dispatch($request));
    }

    public function testGetOriginalRequest()
    {
        $routing = $this->getMockBuilder(\FMUP\Routing::class)->setMethods(array('defaultRoutes'))->getMock();
        $routing->expects($this->exactly(1))->method('defaultRoutes')->willReturn($routing);

        $request = $this->getMockBuilder(\FMUP\Request::class)->getMock();
        /** @var $request \FMUP\Request */
        /** @var $routing \FMUP\Routing */
        $routing->dispatch($request);
        $originalRequest = $routing->getOriginalRequest();
        $this->assertEquals($request, $originalRequest);
        $this->assertNotSame($request, $originalRequest);
    }

    public function testAddRoute()
    {
        $routing = $this->getMockBuilder(\FMUP\Routing::class)->setMethods(null)->getMock();

        $route = $this->getMockBuilder(\FMUP\Routing\Route::class)->getMock();
        $route2 = $this->getMockBuilder(\FMUP\Routing\Route::class)->getMock();
        $route3 = $this->getMockBuilder(\FMUP\Routing\Route::class)->getMock();
        /**
         * @var $routing \FMUP\Routing
         * @var $route \FMUP\Routing\Route
         * @var $route2 \FMUP\Routing\Route
         * @var $route3 \FMUP\Routing\Route
         */
        $routing->addRoute($route2, \FMUP\Routing::WAY_APPEND);
        $routing->addRoute($route3);
        $routing->addRoute($route, \FMUP\Routing::WAY_PREPEND);

        $routeList = array($route, $route2, $route3);
        $this->assertSame($routeList, $routing->getRoutes());
    }

    public function testClearRoute()
    {
        $routing = $this->getMockBuilder(\FMUP\Routing::class)->setMethods(null)->getMock();

        $route = $this->getMockBuilder(\FMUP\Routing\Route::class)->getMock();
        $route2 = $this->getMockBuilder(\FMUP\Routing\Route::class)->getMock();
        $route3 = $this->getMockBuilder(\FMUP\Routing\Route::class)->getMock();
        /**
         * @var $routing \FMUP\Routing
         * @var $route \FMUP\Routing\Route
         * @var $route2 \FMUP\Routing\Route
         * @var $route3 \FMUP\Routing\Route
         */
        $routing->addRoute($route2);
        $routing->addRoute($route3);
        $routing->addRoute($route, \FMUP\Routing::WAY_PREPEND);

        $routeList = array();
        $this->assertSame($routing, $routing->clearRoutes());
        $this->assertSame($routeList, $routing->getRoutes());
    }

    public function testDefaultRoutes()
    {
        $routing = new \FMUP\Routing;
        $this->assertSame($routing, $routing->defaultRoutes());
    }

    public function testDispatchWithoutRedispatchFirstRoute()
    {
        $routing = $this->getMockBuilder(\FMUP\Routing::class)->setMethods(null)->getMock();

        $route = $this->getMockBuilder(\FMUP\Routing\Route::class)->getMock();
        $route->method('setRequest')->willReturn($route);
        $route->method('canHandle')->willReturn(false);
        $route2 = $this->getMockBuilder(\FMUP\Routing\Route::class)->getMock();
        $route2->method('setRequest')->willReturn($route2);
        $route2->method('canHandle')->willReturn(true);
        $route2->method('handle')->willReturn(true);
        $route2->method('hasToBeRedispatched')->willReturn(false);
        $route3 = $this->getMockBuilder(\FMUP\Routing\Route::class)->getMock();
        $route3->method('setRequest')->willReturn($route3);
        $route3->method('canHandle')->willReturn(true);
        $route3->method('handle')->willReturn(true);
        $route3->method('hasToBeRedispatched')->willReturn(false);
        $request = $this->getMockBuilder(\FMUP\Request::class)->getMock();
        /**
         * @var $routing \FMUP\Routing
         * @var $route \FMUP\Routing\Route
         * @var $route2 \FMUP\Routing\Route
         * @var $route3 \FMUP\Routing\Route
         * @var $request \FMUP\Request
         */
        $routing->addRoute($route2, \FMUP\Routing::WAY_APPEND);
        $routing->addRoute($route3);
        $routing->addRoute($route, \FMUP\Routing::WAY_PREPEND);

        $routeList = array($route, $route2, $route3);
        $this->assertSame($routeList, $routing->getRoutes());
        $this->assertSame($route2, $routing->dispatch($request));
    }

    public function testDispatchWithRedispatchLastRoute()
    {
        $routing = $this->getMockBuilder(\FMUP\Routing::class)->setMethods(null)->getMock();

        $route = $this->getMockBuilder(\FMUP\Routing\Route::class)->getMock();
        $route->method('setRequest')->willReturn($route);
        $route->method('canHandle')->willReturn(false);
        $route2 = $this->getMockBuilder(\FMUP\Routing\Route::class)->getMock();
        $route2->method('setRequest')->willReturn($route2);
        $route2->method('canHandle')->will($this->onConsecutiveCalls(false, true));
        $route2->method('handle')->willReturn(true);
        $route2->method('hasToBeRedispatched')->willReturn(false);
        $route3 = $this->getMockBuilder(\FMUP\Routing\Route::class)->getMock();
        $route3->method('setRequest')->willReturn($route3);
        $route3->method('canHandle')->willReturn(true);
        $route3->method('handle')->willReturn(true);
        $route3->method('hasToBeRedispatched')->willReturn(true);
        $request = $this->getMockBuilder(\FMUP\Request::class)->getMock();
        /**
         * @var $routing \FMUP\Routing
         * @var $route \FMUP\Routing\Route
         * @var $route2 \FMUP\Routing\Route
         * @var $route3 \FMUP\Routing\Route
         * @var $request \FMUP\Request
         */
        $routing->addRoute($route2, \FMUP\Routing::WAY_APPEND);
        $routing->addRoute($route3);
        $routing->addRoute($route, \FMUP\Routing::WAY_PREPEND);

        $routeList = array($route, $route2, $route3);
        $this->assertSame($routeList, $routing->getRoutes());
        $this->assertSame($route2, $routing->dispatch($request));
    }
}
