<?php
namespace FMUP;

/**
 * Class ErrorHandler
 * @package FMUP
 */
class ErrorHandler
{
    private $handlers = array();
    private $response;
    private $request;
    private $bootstrap;

    /**
     * @param ErrorHandler\Abstraction $handler
     * @return $this
     */
    public function add(ErrorHandler\Abstraction $handler)
    {
        array_push($this->handlers, $handler);
        return $this;
    }

    /**
     * @return array
     */
    public function get()
    {
        return $this->handlers;
    }

    /**
     * @return $this
     */
    public function clear()
    {
        $this->handlers = array();
        return $this;
    }

    /**
     * @param \Exception $e
     * @return $this
     * @throws Exception
     * @throws \Exception
     */
    public function handle(\Exception $e)
    {
        if (!count($this->get())) {
            throw $e;
        }
        foreach ($this->get() as $handler) {
            /* @var $handler ErrorHandler\Abstraction */
            $handler->setResponse($this->getResponse())
                ->setRequest($this->getRequest())
                ->setBootstrap($this->getBootstrap())
                ->setException($e);
            if ($handler->canHandle()) {
                $handler->handle();
            }
        }
        return $this;
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
            throw new Exception('Unable to access response. Not set');
        }
        return $this->response;
    }

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
            throw new Exception('Unable to access request. Not set');
        }
        return $this->request;
    }

    /**
     * @param Bootstrap $bootstrap
     * @return $this
     */
    public function setBootstrap(Bootstrap $bootstrap)
    {
        $this->bootstrap = $bootstrap;
        return $this;
    }

    /**
     * @return Bootstrap
     * @throws Exception
     */
    public function getBootstrap()
    {
        if (!$this->bootstrap) {
            throw new Exception('Unable to access bootstrap. Not set');
        }
        return $this->bootstrap;
    }
}
