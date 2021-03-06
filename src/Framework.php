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
        return $this->routingSystem = $this->routingSystem ?: new Routing();
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response = $this->response ?: new Response();
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
        throw new NotFound('Controller not found ' . $directory . '/' . $controller);
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
        if (!is_callable(array($controllerInstance, $callable))) {
            throw new Exception\Status\NotFound("Undefined function $callable");
        }
        $actionReturn = call_user_func(array($controllerInstance, $callable));
        $controllerInstance->postFilter($action);

        if (!is_null($actionReturn)) {
            $controllerInstance->getResponse()
                ->setBody($actionReturn instanceof View ? $actionReturn->render() : $actionReturn);
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
            $this->getResponse()->addHeader(new Response\Header\Location($exception->getLocation()));
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

    /**
     * @return ErrorHandler\Plugin\Mail
     * @codeCoverageIgnore
     */
    protected function createPluginMail()
    {
        return new ErrorHandler\Plugin\Mail();
    }

    /**
     * @param int $code
     * @param string $msg
     * @param null $errFile
     * @param int $errLine
     * @param array $errContext
     */
    public function errorHandler($code, $msg, $errFile = null, $errLine = 0, array $errContext = array())
    {
        $block = E_PARSE | E_ERROR | E_USER_ERROR;
        $binary = $code & $block;
        $message = $msg . ' in file ' . $errFile . ' on line ' . $errLine;
        if ($binary) {
            $message .= ' {' . serialize($errContext) . '}';
            $this->createPluginMail()
                ->setBootstrap($this->getBootstrap())
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
            E_ALL => Logger::CRITICAL,
            E_COMPILE_ERROR => Logger::ERROR,
            E_COMPILE_WARNING => Logger::WARNING,
            E_CORE_ERROR => Logger::ERROR,
            E_CORE_WARNING => Logger::WARNING,
            E_USER_NOTICE => Logger::NOTICE,
        );
        $this->getBootstrap()->getLogger()->log(Logger\Channel\System::NAME, $translate[$code], $message, $errContext);
        if ($binary && $this->getSapi()->get() == Sapi::CLI) {
            $this->phpExit($binary);
        }
    }

    /**
     * @param int $code
     * @codeCoverageIgnore
     */
    protected function phpExit($code)
    {
        exit($code);
    }

    /**
     * @return ErrorHandler
     */
    public function getErrorHandler()
    {
        return $this->errorHandler = $this->errorHandler ?: new ErrorHandler\Base();
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
        $this->getPreDispatcherSystem()->dispatch($this->getRequest(), $this->getResponse());
        return $this;
    }

    /**
     * Treatment on response if needed
     */
    protected function postDispatch()
    {
        $this->getPostDispatcherSystem()->dispatch($this->getRequest(), $this->getResponse());
        return $this;
    }

    /**
     * @todo rewrite to be SOLID
     */
    public function shutDown()
    {
        $error = $this->errorGetLast();
        $isDebug = $this->isDebug();
        $code = E_PARSE | E_ERROR | E_USER_ERROR;
        $canHeader = $this->getSapi()->get() != Sapi::CLI;
        if ($error !== null && ($error['type'] & $code)) {
            $this->errorHandler($code, $error['message'], $error['file'], $error['line']);
            if ($canHeader) {
                $this->getErrorHeader()->render();
                if (!$isDebug) {
                    echo "<br/>Une erreur est survenue !<br/>"
                        . "Le support informatique a été prévenu "
                        . "et règlera le problême dans les plus brefs délais.<br/>"
                        . "<br/>"
                        . "L'équipe des développeurs vous prie de l'excuser pour le désagrément.<br/>";
                }
            }
        }
    }

    /**
     * @return Response\Header\Status
     * @codeCoverageIgnore
     */
    protected function getErrorHeader()
    {
        return new Response\Header\Status(Response\Header\Status::VALUE_INTERNAL_SERVER_ERROR);
    }

    /**
     * @return array
     * @codeCoverageIgnore
     */
    protected function errorGetLast()
    {
        return error_get_last();
    }

    /**
     * @return bool
     * @codeCoverageIgnore
     */
    protected function isDebug()
    {
        return (bool)ini_get('display_errors');
    }

    /**
     * @return Dispatcher
     */
    public function getPreDispatcherSystem()
    {
        return $this->preDispatcherSystem = $this->preDispatcherSystem ?: new Dispatcher();
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
        return $this->postDispatcherSystem = $this->postDispatcherSystem ?: new Dispatcher\Post();
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
        return $this->bootstrap = $this->bootstrap ?: new Bootstrap;
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
