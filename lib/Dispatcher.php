<?php
namespace FMUP;

use FMUP\Dispatcher\Plugin;
use FMUP\Environment;
use FMUP\Sapi;

class Dispatcher
{
    use Environment\OptionalTrait, Sapi\OptionalTrait;

    const WAY_APPEND = 'WAY_APPEND';
    const WAY_PREPEND = 'WAY_PREPEND';

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
     * Dispatch routes and return the first available route
     * @param Request $request
     * @param Response $response
     * @return $this
     */
    public function dispatch(Request $request, Response $response)
    {
        $this->setOriginalRequest($request);
        $this->defaultPlugins();
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
     * @param string $way
     * @return $this
     */
    public function addPlugin(Plugin $plugin, $way = self::WAY_APPEND)
    {
        if ($way == self::WAY_APPEND) {
            array_push($this->plugins, $plugin);
        } else {
            array_unshift($this->plugins, $plugin);
        }
        return $this;
    }

    /**
     * Initialize default plugins to define - optional
     * @return $this
     */
    public function defaultPlugins()
    {
        return $this;
    }
}
