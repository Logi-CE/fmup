<?php
namespace FMUP\Dispatcher;

use FMUP\Environment;
use FMUP\Exception;
use FMUP\Request;
use FMUP\Response;
use FMUP\Sapi;

/**
 * Class Route - Route handling
 * @package FMUP\Routing
 */
abstract class Plugin
{
    use Environment\OptionalTrait, Sapi\OptionalTrait;
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var string
     */
    protected $name;

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

    /**
     * Get the name of the plugin
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
