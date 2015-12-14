<?php
namespace FMUP;

use FMUP\Exception\Status\NotFound;

/**
 * Class Framework - extends FMU
 * @package FMUP
 */
class Framework extends \Framework
{
    use Config\OptionalTrait;

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
     * @var ErrorHandler
     */
    private $errorHandler;

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
            $this->request = ($this->getSapi()->get() == Sapi::CLI ? new Request\Cli() : new Request\Http());
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
     * @param string $directory
     * @param string $controller
     * Real 404 errors
     * @throws NotFound
     */
    public function getRouteError($directory, $controller)
    {
        throw new NotFound('Controller not found' . $directory . '/' . $controller);
    }

    /**
     * @param string $controllerName
     * @param string $action
     * @return Controller
     * @throws Exception\Status\NotFound
     */
    protected function instantiate($controllerName, $action)
    {
        //To be compliant with old system @todo
        if (!class_exists($controllerName)) {
            throw new Exception\Status\NotFound('Controller does not exist');
        }
        /* @var $controllerInstance Controller */
        $controllerInstance = new $controllerName();
        $controllerInstance
            ->setRequest($this->getRequest())
            ->setResponse($this->getResponse())
            ->setBootstrap($this->getBootstrap());

        $controllerInstance->preFilter($action);
        $callable = $controllerInstance->getActionMethod($action);
        $actionReturn = null;
        if (is_callable(array($controllerInstance, $callable))) {
            $actionReturn = call_user_func(array($controllerInstance, $callable));
        } else {
            throw new Exception\Status\NotFound("Undefined function $callable");
        }
        $controllerInstance->postFilter($action);

        if (!is_null($actionReturn)) {
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
            $this->preDispatch();
            parent::dispatch();
        } catch (Exception\Location $exception) {
            $this->getResponse()
                ->addHeader(
                    new Response\Header\Location($exception->getLocation())
                );
        } catch (\Exception $exception) {
            $this->getErrorHandler()
                ->setBootstrap($this->getBootstrap())
                ->setRequest($this->getRequest())
                ->setResponse($this->getResponse())
                ->handle($exception);
        }
        $this->postDispatch();
        return $this;
    }

    public function errorHandler($code, $msg, $errFile = null, $errLine = 0, array $errContext = array())
    {
        $block = E_PARSE | E_ERROR | E_USER_ERROR;
        $binary = $code & $block;
        if ($binary) {
            $message = $msg . ' in file ' . $errFile . ' on line ' . $errLine;
            if ($errContext) {
                $message .= ' {' . serialize($errContext) . '}';
            }
            $fmupMail = new ErrorHandler\Plugin\Mail();
            $fmupMail->setBootstrap($this->getBootstrap())
                ->setRequest($this->getRequest())
                ->setException(new Exception($message, $code))
                ->handle();
        }

        $translate = array(
            E_NOTICE => Logger::NOTICE,
            E_WARNING => Logger::WARNING,
            E_ERROR => Logger::ERROR,
            E_PARSE => Logger::CRITICAL,
            E_DEPRECATED => Logger::INFO,
            E_USER_ERROR => Logger::NOTICE,
            E_USER_WARNING => Logger::WARNING,
            E_USER_ERROR => Logger::ERROR,
            E_USER_DEPRECATED => Logger::INFO,
            E_STRICT => Logger::INFO,
            E_RECOVERABLE_ERROR => Logger::ERROR,
        );
        $level = isset($translate[$code]) ? $translate[$code] : Logger::ALERT;
        $message = $msg . ' in ' . $errFile . ' on line ' . $errLine;
        $this->getBootstrap()->getLogger()->log(Logger\Channel\System::NAME, $level, $message, $errContext);
    }

    /**
     * @return ErrorHandler
     */
    public function getErrorHandler()
    {
        if (!$this->errorHandler) {
            $this->errorHandler = new ErrorHandler\Base();
        }
        return $this->errorHandler;
    }

    /**
     * @param ErrorHandler $errorHandler
     * @return $this
     */
    public function setErrorHandler(ErrorHandler $errorHandler)
    {
        $this->errorHandler = $errorHandler;
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
     * @todo rewrite to be SOLID
     */
    public function shutDown()
    {
        $error = error_get_last();
        $isDebug = ini_get('display_errors');
        $code = E_PARSE | E_ERROR | E_USER_ERROR;
        $canHeader = $this->getSapi()->get() != Sapi::CLI;
        if ($error !== null && ($error['type'] & $code) && $canHeader) {
            $this->errorHandler($code, $error['message'], $error['file'], $error['line']);
            $errorHeader = new Response\Header\Status(Response\Header\Status::VALUE_INTERNAL_SERVER_ERROR);
            $errorHeader->render();
            if (!$isDebug) {
                echo \Constantes::getMessageErreurApplication();
            }
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

    public function initialize()
    {
        if (!$this->getBootstrap()->hasSapi()) {
            $this->getBootstrap()->setSapi($this->getSapi());
        }
        if (!$this->getBootstrap()->hasRequest()) {
            $this->getBootstrap()->setRequest($this->getRequest());
        }
        if (!$this->getBootstrap()->hasConfig()) {
            $this->getBootstrap()->setConfig($this->getConfig());
        }
        $this->getBootstrap()->warmUp();
        parent::initialize();
    }
}
