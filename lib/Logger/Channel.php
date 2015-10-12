<?php
namespace FMUP\Logger;

use Monolog\Logger as MonologLogger;
use FMUP\Config;
use FMUP\Request;
use FMUP\Response;
use FMUP\Environment;

abstract class Channel
{
    /**
     * @var MonologLogger
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var Environment
     */
    private $environment;

    /**
     * Name of the channel
     * @return string
     */
    abstract public function getName();

    /**
     * Must configure the logger channel
     * @return MonologLogger
     */
    abstract public function configure();

    /**
     * Retrieve defined logger
     * @return MonologLogger
     */
    public function getLogger()
    {
        if (!isset($this->logger)) {
            $this->logger = new MonologLogger($this->getName());
            $this->configure();
        }
        return $this->logger;
    }

    /**
     * @param MonologLogger $logger
     * @return $this
     */
    public function setLogger(MonologLogger $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return Config
     * @throws Exception
     */
    public function getConfig()
    {
        if (!$this->config) {
            throw new Exception('Config is not defined');
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

    /**
     * Check whether config exists
     * @return bool
     */
    public function hasConfig()
    {
        return (bool) $this->config;
    }

    /**
     * Define environment
     * @param Environment $environment
     * @return $this
     */
    public function setEnvironment(Environment $environment)
    {
        $this->environment = $environment;
        return $this;
    }

    /**
     * @return Environment
     * @throws Exception
     */
    public function getEnvironment()
    {
        if (!$this->environment) {
            $this->environment = Environment::getInstance();
            if ($this->hasConfig()) {
                $this->environment->setConfig($this->getConfig());
            }
        }
        return $this->environment;
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

    public function getRequest()
    {
        if (!$this->request) {
            throw new Exception("Request must be defined");
        }
        return $this->request;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }

    public function getResponse()
    {
        if (!$this->response) {
            throw new Exception('Response must be defined');
        }
        return $this->response;
    }
}
