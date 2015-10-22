<?php
namespace FMUP\Routing;

use FMUP\Exception;
use FMUP\Request;

/**
 * Class Route - Route handling
 * @package FMUP\Routing
 */
abstract class Route
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return Request
     * @throws Exception
     */
    public function getRequest()
    {
        if (!$this->request) {
            throw new Exception('Route Request not set');
        }
        return $this->request;
    }

    /**
     * Must return true if URI can be handled by route
     * @return bool
     */
    abstract public function canHandle();

    /**
     * Must return Controller class name
     * @return string
     */
    abstract public function getControllerName();

    /**
     * Must return action to call
     * @return string
     */
    abstract public function getAction();

    /**
     * Can be used to apply something on request object
     */
    public function handle()
    {

    }

    /**
     * Must return true in route has modified request be must be rechecked in dispatch loop
     * WARNING : if this method returns true, please be aware that infinite loop might be triggered
     * @return bool
     */
    public function hasToBeReDispatched()
    {
        return false;
    }
}
