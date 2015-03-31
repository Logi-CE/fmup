<?php
namespace FMUP\Dispatcher;

use FMUP\Exception;
use FMUP\Request;
use FMUP\Response;

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
     * @var Response
     */
    private $response;

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
            throw new Exception('Request not set');
        }
        return $this->request;
    }

    /**
     * @param Response $response
     * @return $this
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return Response
     * @throws Exception
     */
    public function getResponse()
    {
        if (!$this->response) {
            throw new Exception('Response not set');
        }
        return $this->response;
    }

    /**
     * Can be used to apply something on request object
     */
    abstract public function handle();
}
