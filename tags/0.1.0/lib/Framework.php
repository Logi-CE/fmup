<?php
namespace FMUP;
require_once BASE_PATH . '/system/framework.php';
//fix compliance for DB in model
\Model::setDb(Helper\Db::getInstance());
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
     * @param string $controllerName
     * @param string $action
     * @throws \NotFoundError
     * @return \Controller
     */
    protected function instantiate($controllerName, $action)
    {
        //To be compliant with old system @todo
        global $sys_controller_instance;

        if (!class_exists($controllerName)) {
            throw new \NotFoundError('Controller does not exist');
        }
        /* @var $controllerInstance \Controller */
        $controllerInstance = new $controllerName();
        $controllerInstance->setDb(Helper\Db::getInstance()); //to be compliant with old system - DB should not be in controller @todo

        $sys_controller_instance = $controllerInstance; //to be compliant with old system @todo
        if ($controllerInstance instanceof Controller) {
            /* @var $controllerInstance Controller */
            $controllerInstance
                ->setRequest($this->getRequest())
                ->setResponse($this->getResponse());
        }
        $controllerInstance->preFiltre();
        $actionReturn = null;
        if (method_exists($controllerInstance, $action)) {
            $actionReturn = call_user_func(array($controllerInstance, $action));
        } else {
            throw new \NotFoundError(\Error::fonctionIntrouvable($action));
        }
        $controllerInstance->postFiltre();

        if ($controllerInstance instanceof Controller && !is_null($actionReturn)) {
            $controllerInstance->getResponse()
                ->setBody(
                    $actionReturn instanceof \FMUP\View ? $actionReturn->render() : $actionReturn
                );
        }
        return $controllerInstance;
    }

    /**
     * Treatment on response if needed
     * @param \Controller $controller
     */
    protected function postDispatch(\Controller $controller)
    {
        parent::postDispatch($controller);
        if ($controller instanceof Controller) {
            $controller->getResponse()->send();
        }
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
                if (!\Config::isDebug() && in_array($error['type'], array(E_PARSE, E_ERROR, E_USER_ERROR))) {
                    echo \Constantes::getMessageErreurApplication();
                }
            }
            exit();
        }
    }
}
