<?php
namespace FMUP;

use FMUP\Exception\Status\NotFound;

require_once __DIR__ . '/../system/framework.php';

if (!defined('BASE_PATH')) {
    define('BASE_PATH', implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', '..', '..', '..')));
}

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
     * @var Config
     */
    private $config;
    /**
     * @var Sapi
     */
    private $sapi;

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
     * @param Sapi $sapi
     * @return $this
     */
    public function setSapi(Sapi $sapi)
    {
        $this->sapi = $sapi;
        return $this;
    }

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
     * Define a config to use
     * @param Config $config
     * @return $this
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Retrieve defined config
     * @return Config
     */
    public function getConfig()
    {
        if (!$this->config) {
            $this->config = new Config;
        }
        return $this->config;
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
            $fmupMail = new \FMUP\ErrorHandler\Plugin\Mail();
            $fmupMail->setBootstrap($this->getBootstrap())
                ->setRequest($this->getRequest())
                ->setException(new \FMUP\Exception($message, $code))
                ->handle();
        }

        $translate = array(
            E_NOTICE => \Monolog\Logger::NOTICE,
            E_WARNING => \Monolog\Logger::WARNING,
            E_ERROR => \Monolog\Logger::ERROR,
            E_PARSE => \Monolog\Logger::CRITICAL,
            E_DEPRECATED => \Monolog\Logger::INFO,
            E_USER_ERROR => \Monolog\Logger::NOTICE,
            E_USER_WARNING => \Monolog\Logger::WARNING,
            E_USER_ERROR => \Monolog\Logger::ERROR,
            E_USER_DEPRECATED => \Monolog\Logger::INFO,
            E_STRICT => \Monolog\Logger::INFO,
            E_RECOVERABLE_ERROR => \Monolog\Logger::ERROR,
        );
        $level = isset($translate[$code]) ? $translate[$code] : \Monolog\Logger::ALERT;
        $message = $msg . ' in ' . $errFile . ' on line ' . $errLine;
        $this->getBootstrap()->getLogger()->log(\FMUP\Logger\Channel\System::NAME, $level, $message, $errContext);
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
     * @return $this
     */
    public function registerErrorHandler()
    {
        if (
            $this->getBootstrap()->getConfig()->get('is_debug') ||
            !$this->getBootstrap()->getConfig()->get('use_daily_alert')
        ) {
            parent::registerErrorHandler();
        }
        return $this;
    }

    /**
     * @todo rewrite to be SOLID
     */
    public function shutDown()
    {
        $error = error_get_last();
        $isDebug = $this->getBootstrap()->getConfig()->get('is_debug');
        $code = E_PARSE | E_ERROR | E_USER_ERROR;
        $canHeader = $this->getSapi()->get() != Sapi::CLI;
        if ($error !== null && ($error['type'] & $code) && $canHeader) {
            $this->errorHandler($code, $error['message'], $error['file'], $error['line']);
            $errorHeader = new \FMUP\Response\Header\Status(\FMUP\Response\Header\Status::VALUE_INTERNAL_SERVER_ERROR);
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

    /**
     * @return $this
     */
    protected function definePhpIni()
    {
        if ($this->getBootstrap()->getConfig()->get('use_daily_alert')) {
            ini_set('error_reporting', E_ALL);
            ini_set('display_errors', $this->getBootstrap()->getConfig()->get('is_debug'));
            ini_set('display_startup_errors', $this->getBootstrap()->getConfig()->get('is_debug'));
            ini_set('html_errors', $this->getBootstrap()->getConfig()->get('is_debug'));
        } else {
            parent::definePhpIni();
        }
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

        \Config::getInstance()->setFmupConfig($this->getBootstrap()->getConfig()); //to be compliant with old system @todo delete
        \Model::setDb(Helper\Db::getInstance()->get()); //@todo find a better solution
        parent::initialize();
    }
}
