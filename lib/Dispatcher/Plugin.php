<?php
namespace FMUP\Dispatcher;

use FMUP\Exception;
use FMUP\Request;
use FMUP\Response;
use FMUP\Sapi;
use FMUP\Environment;

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
     * @var Sapi
     */
    private $sapi;

    /**
     * @var Environment
     */
    private $environment;

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
     * @param Sapi $sapi
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
     * @return Environment
     */
    public function getEnvironment()
    {
        if (!$this->environment) {
            $this->environment = Environment::getInstance();
        }
        return $this->environment;
    }

    /**
     * @param Environment $environment
     * @return $this
     */
    public function setEnvironment(Environment $environment)
    {
        $this->environment = $environment;
        return $this;
    }

    /**
     * Check if plugin can handle request/response
     * @return bool
     */
    public function canHandle()
    {
        return true;
    }

    /**
     * Can be used to apply something on request object
     */
    abstract public function handle();
}
