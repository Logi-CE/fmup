<?php
namespace FMUP;

use FMUP\Dispatch\Plugin;

class Dispatcher
{
    /**
     * List of routes to check on routing
     * @var array
     */
    private $plugins = array();

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
     * @param Response $response
     */
    public function dispatch(Request $request, Response $response)
    {
        $this->setOriginalRequest($request);
        foreach ($this->plugins as $plugin) {
            /* @var $plugin Plugin */
            $plugin->setRequest($request)->setResponse($response)->handle();
        }
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
    public function clear()
    {
        $this->plugins = array();
        return $this;
    }

    /**
     * Add a plugin in stack
     * @param Plugin $plugin
     * @return $this
     */
    public function addPlugin(Plugin $plugin)
    {
        array_push($this->plugins, $plugin);
        return $this;
    }
}
