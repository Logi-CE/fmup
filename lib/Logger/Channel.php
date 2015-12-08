<?php
namespace FMUP\Logger;

use FMUP\Config;
use FMUP\Environment;
use FMUP\Request;
use FMUP\Response;
use Monolog\Logger as MonologLogger;

abstract class Channel
{
    use Environment\OptionalTrait {
        getEnvironment as getEnvironmentTrait;
    }
    use Config\OptionalTrait;

    /**
     * @var MonologLogger
     */
    private $logger;

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
     * @return Environment
     * @throws Exception
     */
    public function getEnvironment()
    {
        if (!$this->hasEnvironment()) {
            $environment = Environment::getInstance();
            if ($this->hasConfig()) {
                $environment->setConfig($this->getConfig());
            }
            $this->setEnvironment($environment);
        }
        return $this->getEnvironmentTrait();
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
        return $this->getLogger()->addRecord((int)$level, (string)$message, (array)$context);
    }
}
