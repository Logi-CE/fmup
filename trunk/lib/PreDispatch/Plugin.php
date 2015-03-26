<?php
namespace FMUP\PreDispatch;

use FMUP\Exception;
use FMUP\Request;

/**
 * Class Route - Route handling
 * @package FMUP\Routing
 */
abstract class Plugin
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
     * Can be used to apply something on request object
     */
    abstract public function handle();
}
