<?php
namespace FMUP\Logger;

use Monolog\Logger;
use FMUP\Config;
use FMUP\Request;
use FMUP\Response;

abstract class Channel
{
    /**
     * @var Logger
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
     * Name of the channel
     * @return string
     */
    abstract public function getName();

    /**
     * Must configure the logger channel
     * @return Logger
     */
    abstract public function configure();

    /**
     * Retrieve defined logger
     * @return Logger
     */
    public function getLogger()
    {
        if (!isset($this->logger)) {
            $this->logger = new Logger($this->getName());
            $this->configure();
        }
        return $this->logger;
    }

    /**
     * @param Logger $logger
     * @return $this
     */
    public function setLogger(Logger $logger)
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

    /**
     * Add a message in logger
     * @param $level
     * @param $message
     * @param array $context
     * @return bool
     */
    public function addRecord($level, $message, array $context = array())
    {
        return $this->getLogger()->addRecord((int) $level,(string) $message, (array)$context);
    }
}
