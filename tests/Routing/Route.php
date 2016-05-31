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
        $request = $this->getMock(\FMUP\Request\Cli::class);
        $route = $this->getMock(\FMUP\Routing\Route::class, array('canHandle', 'getControllerName', 'getAction'));
        /**
         * @var \FMUP\Routing\Route $route
         * @var \FMUP\Request $request
         */
        $this->assertSame($route, $route->setRequest($request));
        $this->assertSame($request, $route->getRequest());
    }

    public function testFailGetRequest()
    {
        $request = $this->getMock(\FMUP\Request\Cli::class);
        $route = $this->getMock(\FMUP\Routing\Route::class, array('canHandle', 'getControllerName', 'getAction'));
        /**
         * @var \FMUP\Routing\Route $route
         * @var \FMUP\Request $request
         */
        $this->expectException(\FMUP\Exception::class);
        $this->expectExceptionMessage('Route Request not set');
        $this->assertSame($request, $route->getRequest());
    }

    public function testHandle()
    {
        $route = $this->getMock(\FMUP\Routing\Route::class, array('canHandle', 'getControllerName', 'getAction'));
        /** @var \FMUP\Routing\Route $route */
        $route->handle();
        $this->assertTrue(true);
    }

    public function testHasToBeRedispatched()
    {
        $route = $this->getMock(\FMUP\Routing\Route::class, array('canHandle', 'getControllerName', 'getAction'));
        /** @var \FMUP\Routing\Route $route */
        $this->assertFalse($route->hasToBeReDispatched());
    }
}
