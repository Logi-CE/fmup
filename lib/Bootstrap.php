<?php
namespace FMUP;

class Bootstrap
{
    private $isErrorHandlerRegistered = false;
    private $logger;
    private $request;
    private $session;
    private $config;
    private $flashMessenger;
    private $isWarmed;

    /**
     * Prepare needed configuration in bootstrap.
     *
     * There is no need to warm up DB connection but it could be configured
     *
     * @return $this
     */
    public function warmUp()
    {
        if (!$this->isWarmed()) {
            $this->initHelperDb()->getLogger();
            //$this->registerErrorHandler(); //@todo activation of this might be very useful
            $this->setIsWarmed();
        }
        return $this;
    }

    /**
     * Initialize Config in helper db
     * @return $this
     */
    private function initHelperDb()
    {
        Helper\Db::setConfig($this->getConfig());//@todo find a better solution
        return $this;
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        if (!$this->session) {
            $this->session = Session::getInstance();
        }
        return $this->session;
    }

    /**
     * Define session component
     * @param Session $session
     * @return $this
     */
    public function setSession(Session $session)
    {
        $this->session = $session;
        return $this;
    }

    /**
     * Return logger
     * @return Logger
     */
    public function getLogger()
    {
        if (!$this->logger) {
            $this->logger = new Logger();
            $this->logger->setRequest($this->getRequest())->setConfig($this->getConfig());
        }
        return $this->logger;
    }

    /**
     * Define logger
     * @param Logger $logger
     * @return $this
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    public function registerErrorHandler()
    {
        if (!$this->isErrorHandlerRegistered) {
            \Monolog\ErrorHandler::register($this->getLogger()->get(\FMUP\Logger\Channel\System::NAME));
            $this->isErrorHandlerRegistered = true;
        }
        return $this;
    }

    /**
     * Define HTTP request object
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Retrieve defined HTTP request object
     * @return Request
     * @throws \LogicException if no request has been set
     */
    public function getRequest()
    {
        if (!$this->hasRequest()) {
            throw new \LogicException('Request is not defined');
        }
        return $this->request;
    }

    /**
     * Check if request is defined
     * @return bool
     */
    public function hasRequest()
    {
        return !is_null($this->request);
    }


    /**
     * Get flashMessenger
     * @return \FMUP\FlashMessenger
     */
    public function getFlashMessenger()
    {
        if ($this->flashMessenger === null) {
            $this->flashMessenger = FlashMessenger::getInstance();
        }
        return $this->flashMessenger;
    }

    /**
     * @param FlashMessenger $flashMessenger
     * @return $this
     */
    public function setFlashMessenger(FlashMessenger $flashMessenger)
    {
        $this->flashMessenger = $flashMessenger;
        return $this;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        if (!$this->hasConfig()) {
            throw new \LogicException('Config is not defined');
        }
        return $this->config;
    }

    /**
     * @param Config $config
     * @return $this
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }

    public function hasConfig()
    {
        return !is_null($this->config);
    }

    /**
     * @return bool
     */
    public function isWarmed()
    {
        return (bool)$this->isWarmed;
    }

    /**
     * @return $this
     */
    public function setIsWarmed()
    {
        $this->isWarmed = true;
        return $this;
    }
}
