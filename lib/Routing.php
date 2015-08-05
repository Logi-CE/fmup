<?php
namespace FMUP;

use FMUP\Routing\Route;

/**
 * Class Routing - Routing system where we'll be able to handle multiple route to be handled in a controller
 * @package FMUP
 */
class Routing
{
    /**
     * List of routes to check on routing
     * @var array
     */
    private $routes = array();

    /**
     * @var Request
     */
    private $originalRequest;

    /**
     * Construct - may define routes to instantiate
     */
    public function __construct()
    {
    }

    /**
     * Dispatch routes and return the first available route
     * @param Request $request
     * @return Route|null
     */
    public function dispatch(Request $request)
    {
        $this->setOriginalRequest($request);
        $redispatch = false;
        $routeSelected = null;
        do {
            foreach ($this->routes as $route) {
                /* @var $route Route */
                if ($route->setRequest($request)->canHandle()) {
                    $route->handle(); //this will handle the request - not fluent interface because we don't know how developer will write
                    $redispatch = $route->hasToBeReDispatched();
                    $routeSelected = $route;
                    break;
                }
            }
        } while ($redispatch);
        return $routeSelected;
    }

    /**
     * Define the original request
     * @param Request $request
     * @return $this
     */
    private function setOriginalRequest(Request $request)
    {
        $this->originalRequest = clone $request;
        return $this;
    }

    /**
     * Retrieve original request (nothing has been modified)
     * @return Request|null
     */
    public function getOriginalRequest()
    {
        return $this->originalRequest;
    }


    /**
     * Clear all routes defined
     * @return $this
     */
    public function clearRoutes()
    {
        $this->routes = array();
        return $this;
    }

    /**
     * Add a route in stack
     * @param Route $route
     * @return $this
     */
    public function addRoute(Route $route)
    {
        array_push($this->routes, $route);
        return $this;
    }
}
