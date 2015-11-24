<?php
namespace FMUP;

use FMUP\Dispatcher\Plugin;
use FMUP\Sapi;
use FMUP\Environment;

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

    private $sapi;
    private $environment;

    /**
     * Construct - may define routes to instantiate
     */
    public function __construct()
    {
    }

    /**
     * @param \FMUP\Sapi $sapi
     * @return $this
     */
    public function setSapi(Sapi $sapi)
    {
        $this->sapi = $sapi;
        return $this;
    }

    /**
     * @return Sapi
     */
    public function getSapi()
    {
        if (!$this->sapi) {
            $this->sapi = Sapi::getInstance();
        }
        return $this->sapi;
    }

    /**
     * @return \FMUP\Environment
     */
    public function getEnvironment()
    {
        if (!$this->environment) {
            $this->environment = Environment::getInstance();
        }
        return $this->environment;
    }

    /**
     * @param \FMUP\Environment $environment
     * @return $this
     */
    public function setEnvironment(Environment $environment)
    {
        $this->environment = $environment;
        return $this;
    }

    /**
     * Dispatch routes and return the first available route
     * @param Request $request
     * @param Response $response
     * @return $this
     */
    public function dispatch(Request $request, Response $response)
    {
        $this->setOriginalRequest($request);
        foreach ($this->plugins as $plugin) {
            /* @var $plugin Plugin */
            $plugin->setRequest($request)->setResponse($response)
                ->setSapi($this->getSapi())->setEnvironment($this->getEnvironment());
            if ($plugin->canHandle()) {
                $plugin->handle();
            }
        }
        return $this;
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
