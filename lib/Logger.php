<?php
namespace FMUP;

class Logger
{
    private $instances = array();
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Environment
     */
    private $environment;

    protected $factory;

    /**
     * @return Logger\Factory
     */
    public function getFactory()
    {
        if (!$this->factory) {
            $this->factory = Logger\Factory::getInstance();
        }
        return $this->factory;
    }

    /**
     * @param Logger\Factory $factory
     * @return $this
     */
    public function setFactory(Logger\Factory $factory)
    {
        $this->factory = $factory;
        return $this;
    }

    /**
     * @param string $instanceName
     * @return \Monolog\Logger
     */
    public function get($instanceName)
    {
        if (!isset($this->instances[$instanceName])) {
            $channel = $this->getFactory()->getChannel($instanceName);
            $channel->setConfig($this->getConfig())->setEnvironment($this->getEnvironment());
            $this->instances[$instanceName] = $channel;
        }
        return $this->instances[$instanceName];
    }

    /**
     * @param \Monolog\Logger $logger
     * @param string $instanceName
     * @return $this
     */
    public function set(\Monolog\Logger $logger, $instanceName)
    {
        if (!is_null($logger) && !is_null($instanceName)) {
            $this->instances[$instanceName] = $logger;
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
     * @return Config
     */
    public function getConfig()
    {
        if (!$this->config) {
            $this->config = new Config();
        }
        return $this->config;
    }

    /**
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
     */
    public function getEnvironment()
    {
        if (!$this->environment) {
            $this->environment = Environment::getInstance();
            $this->environment->setConfig($this->getConfig());
        }
        return $this->environment;
    }

    /**
     * Add log Record
     * @param string $channel
     * @param int $level
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function log($channel, $level, $message, array $context = array())
    {
        $channelType = $this->get($channel);
        if ($channelType->getName() === \FMUP\Logger\Channel\Standard::NAME) {
            $message = "[Channel $channel] $message";
        }
        return $channelType->addRecord((int) $level, $message, (array) $context);
    }
}
