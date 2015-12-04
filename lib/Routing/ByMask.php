<?php
namespace FMUP\Routing;

use FMUP\Request;
use FMUP\Routing;

abstract class ByMask extends Routing
{
    public function dispatch(Request $request)
    {
        $requestUri = $request->getRequestUri(true);
        foreach ($this->getMasks() as $mask => $routeToLoad) {
            if (
                preg_match('~' . str_replace('~', '\~', $mask) . '~', $requestUri) !== false &&
                class_exists($routeToLoad)
            ) {
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
     * Must return array with expreg as key and Route ClassName as value
     * @return array
     */
    abstract public function getMasks();
}
