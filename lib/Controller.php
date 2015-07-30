<?php
namespace FMUP;

/**
 * Class Controller
 * @package FMUP
 */
abstract class Controller
{
    private $bootstrap;
    private $request;
    private $response;
    private $dbInstance;
    private $view;

    /**
     * this method is called before each action
     * @param string $calledAction
     */
    public function preFilter($calledAction = NULL)
    {
    }

    /**
     * this method is called after each action
     * @param string $calledAction
     */
    public function postFilter($calledAction = NULL)
    {

    }

    /**
     * @throws \LogicException
     * @return Request
     */
    public function getRequest()
    {
        if (!$this->request) {
            throw new \LogicException('Request must be set');
        }
        return $this->request;
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
     * @throws \LogicException
     * @return Response
     */
    public function getResponse()
    {
        if (!$this->response) {
            throw new \LogicException('Response must be set');
        }
        return $this->response;
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
     * Retrieve current view system
     * @return View
     */
    public function getView()
    {
        if (!$this->view) {
            $this->view = new View();
        }
        return $this->view;
    }

    /**
     * Define new view system
     * @param View $view
     * @return $this
     */
    public function setView(View $view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * @throws \LogicException
     * @return Bootstrap
     */
    protected function getBootstrap()
    {
        if (!$this->bootstrap) {
            throw new \LogicException('Bootstrap must be defined');
        }
        return $this->bootstrap;
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
     * @return Session
     */
    protected function getSession()
    {
        return $this->getBootstrap()->getSession();
    }
}
