<?php
namespace FMUP;
use Symfony\Component\DependencyInjection\Exception\LogicException;

/**
 * Class Controller
 * @package FMUP
 */
abstract class Controller extends \Controller
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
    public function preFiltre($calledAction)
    {
    }

    /**
     * @throws LogicException
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
     * @throws LogicException
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
     * @return Helper\Db
     * @deprecated use Helper\Db::getInstance() if REALLY needed. But you'd prefer use of Dependency Injection instead
     */
    public function getDb()
    {
        if (!$this->dbInstance) {
            $this->dbInstance = Helper\Db::getInstance();
        }
        return $this->dbInstance;
    }

    /**
     * Retrieve current view system
     * @return View
     * @throws \Error
     */
    public function getView()
    {
        if (!$this->view) {
            $this->view = new View();
            $this->view
                ->setParam('styles', \Config::paramsVariables('styles'))
                ->setParam('javascripts', \Config::paramsVariables('javascripts'))
            ;
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
