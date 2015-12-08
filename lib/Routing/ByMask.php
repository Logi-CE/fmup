<?php
namespace FMUP\Routing;

use FMUP\Request;
use FMUP\Routing;

/**
 * Allow to match a regexp for a request to a Route
 * @package FMUP\Routing
 */
abstract class ByMask extends Routing
{
    /**
     * Dispatch by mask to a route that will dispatch to correct controller
     * @param Request $request
     * @return Route|null
     */
    public function dispatch(Request $request)
    {
        $requestUri = $request->getRequestUri(true);
        foreach ($this->getMasks() as $mask => $routeToLoad) {
            if (preg_match('~' . str_replace('~', '\~', $mask) . '~', $requestUri) && class_exists($routeToLoad)) {
                $route = new $routeToLoad;
                if ($route instanceof Route) {
                    $this->addRoute($route);
                }
                break;
            }
        }
        return parent::dispatch($request);
    }

    /**
     * Must return array with regexp as key and Route ClassName as value
     * @return array
     */
    abstract public function getMasks();
}
