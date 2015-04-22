<?php
namespace FMUP;


class Bootstrap
{
    private $isErrorHandlerRegistered = false;
    private $logger;
    private $request;

    /**
     * Prepare needed configuration in bootstrap.
     *
     * There is no need to warm up DB connection but it could be configured
     *
     * @return $this
     */
    public function warmUp()
    {
        $this->getLogger();
        //$this->registerErrorHandler(); //@todo activation of this might be very useful but you must clean FMU \Error class and errorhandler before
        return $this;
    }

    /**
     * Return logger system
     * @return Logger
     */
    public function getLogger()
    {
        if (!$this->logger) {
            $this->logger = new Logger();
            $this->logger->setRequest($this->getRequest());
        }
        return $this->logger;
    }

    /**
     * Define logger system
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
            \Monolog\ErrorHandler::register($this->getLogger()->get(Logger::SYSTEM));
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
        if (!$this->request) {
            throw new \LogicException('Request is not defined');
        }
        return $this->request;
    }
}
