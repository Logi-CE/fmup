<?php
namespace FMUP;

use FMUP\Exception\Status\NotFound;

require_once __DIR__ . '/../system/framework.php';


/**
 * Class Framework - extends FMU
 * @package FMUP
 */
class Framework extends \Framework
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
     * @var Routing
     */
    private $routingSystem;
    /**
     * @var Controller\Error
     */
    private $errorController;

    /**
     * @var Dispatcher
     */
    private $preDispatcherSystem;

    /**
     * @var Dispatcher
     */
    private $postDispatcherSystem;
    /**
     * @var Bootstrap
     */
    private $bootstrap;

    /**
     * @param Routing $routingSystem
     * @return $this
     */
    public function setRoutingSystem(Routing $routingSystem)
    {
        $this->routingSystem = $routingSystem;
        return $this;
    }

    /**
     * @return Routing
     */
    public function getRoutingSystem()
    {
        if (!$this->routingSystem) {
            $this->routingSystem = new Routing();
        }
        return $this->routingSystem;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        if (!$this->request) {
            $this->request = new Request();
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
     * @return Response
     */
    public function getResponse()
    {
        if (!$this->response) {
            $this->response = new Response();
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
     * @return array
     * @throws \NotFoundError
     */
    public function getRoute()
    {
        $route = $this->getRoutingSystem()->dispatch($this->getRequest());
        if (is_null($route)) {
            //Dirty fix to be compliant with old system
            return parent::getRoute();
        }
        //dirty to be compliant with old system
        return array($route->getControllerName(), $route->getAction());
    }

    /**
     * Real 404 errors
     * @throws NotFound
     */
    public function getRouteError()
    {
        throw new NotFound('Controller not found');
    }

    /**
     * @param string $controllerName
     * @param string $action
     * @return \Controller|Controller
     * @throws Exception\Status\NotFound
     */
    protected function instantiate($controllerName, $action)
    {
        //To be compliant with old system @todo
        global $sys_controller_instance;
        if (!class_exists($controllerName)) {
            throw new Exception\Status\NotFound('Controller does not exist');
        }
        /* @var $controllerInstance \Controller */
        $controllerInstance = new $controllerName();
        $controllerInstance->setDb(Helper\Db::getInstance()); //to be compliant with old system - DB should not be in controller @todo

        $sys_controller_instance = $controllerInstance; //to be compliant with old system @todo
        if ($controllerInstance instanceof Controller) {
            /* @var $controllerInstance Controller */
            $controllerInstance
                ->setRequest($this->getRequest())
                ->setResponse($this->getResponse())
                ->setBootstrap($this->getBootstrap())
            ;
            $action = $action . 'Action'; //we force action to be a xxxAction
        }

        $controllerInstance->preFiltre($action);
        $actionReturn = null;
        if (method_exists($controllerInstance, $action)) {
            $actionReturn = call_user_func(array($controllerInstance, $action));
        } else {
            throw new Exception\Status\NotFound(\Error::fonctionIntrouvable($action));
        }
        $controllerInstance->postFiltre();

        if ($controllerInstance instanceof Controller && !is_null($actionReturn)) {
            $controllerInstance->getResponse()
                ->setBody(
                    $actionReturn instanceof View ? $actionReturn->render() : $actionReturn
                );
        }
        return $controllerInstance;
    }

    /**
     * @return $this
     */
    protected function dispatch()
    {
        try {
            return parent::dispatch();
        } catch (\Exception $exception) {
            $controller = $this->getErrorController()
                ->setBootstrap($this->getBootstrap())
                ->setRequest($this->getRequest())
                ->setResponse($this->getResponse())
                ->setException($exception);
            $controller->preFiltre('indexAction');
            $controller->indexAction();
            $controller->postFiltre();
            $this->postDispatch();
        }
        return $this;
    }

    /**
     * @return Controller\Error
     */
    public function getErrorController()
    {
        if (!$this->errorController) {
            throw new \LogicException('Error controller must be defined');
        }
        return $this->errorController;
    }

    /**
     * @param Controller\Error $errorController
     * @return $this
     */
    public function setErrorController(Controller\Error $errorController)
    {
        $this->errorController = $errorController;
        return $this;
    }

    /**
     * PreDispatch Request
     * @return $this
     */
    protected function preDispatch()
    {
        $this->getPreDispatcherSystem()
            ->dispatch($this->getRequest(), $this->getResponse());
        return $this;
    }

    /**
     * Treatment on response if needed
     */
    protected function postDispatch()
    {
        $this->getPostDispatcherSystem()
            ->dispatch($this->getRequest(), $this->getResponse());
        return $this;

    }

    /**
     * @return $this
     */
    public function registerErrorHandler()
    {
        if (\Config::isDebug() || !\Config::useDailyAlert()) {
            parent::registerErrorHandler();
        }
        return $this;
    }

    /**
     * @todo rewrite to be SOLID
     */
    public function shutDown()
    {
        if (!\Config::useDailyAlert()) {
            parent::shutDown();
        } else {
            if (\Config::consoleActive()) {
                \Console::finaliser();
            }
            if (($error = error_get_last()) !== null) {
                Error::addContextToErrorLog();
                $isUrgentError = in_array($error['type'], array(E_PARSE, E_ERROR, E_USER_ERROR));
                if ($isUrgentError) {
                    Error::sendMail();
                    if (!\Config::isDebug()) {
                        echo \Constantes::getMessageErreurApplication();
                    }
                }
            }
            exit();
        }
    }

    /**
     * @return Dispatcher
     */
    public function getPreDispatcherSystem()
    {
        if (!$this->preDispatcherSystem) {
            $this->preDispatcherSystem = new Dispatcher();
        }
        return $this->preDispatcherSystem;
    }

    /**
     * @param Dispatcher $preDispatch
     * @return $this
     */
    public function setPreDispatcherSystem(Dispatcher $preDispatch)
    {
        $this->preDispatcherSystem = $preDispatch;
        return $this;
    }

    /**
     * @return Dispatcher
     */
    public function getPostDispatcherSystem()
    {
        if (!$this->postDispatcherSystem) {
            $this->postDispatcherSystem = new Dispatcher\Post();
        }
        return $this->postDispatcherSystem;
    }

    /**
     * @param Dispatcher $postDispatch
     * @return $this
     */
    public function setPostDispatcherSystem(Dispatcher $postDispatch)
    {
        $this->postDispatcherSystem = $postDispatch;
        return $this;
    }

    /**
     * @return Bootstrap
     */
    public function getBootstrap()
    {
        if (!$this->bootstrap) {
            $this->bootstrap = new Bootstrap;
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
     * @throws \Error
     */
    public function initialize()
    {
        $this->getBootstrap()
            ->setRequest($this->getRequest())
            ->warmUp();
        parent::initialize();
    }
}
