<?php
namespace FMUP;

use Monolog\Formatter\HtmlFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\WebProcessor;

class Logger
{
    const ERROR = 'ERROR';
    const SYSTEM = 'SYSTEM';

    private $instances = array();
    private $request;

    /**
     * @param string $instanceName
     * @return \Monolog\Logger
     */
    public function get($instanceName = self::SYSTEM)
    {
        if (!isset($this->instances[$instanceName])) {
            $logger = new \Monolog\Logger($instanceName);
            $this->configureDefault($logger, $instanceName);
            $this->instances[$instanceName] = $logger;
        }
        return $this->instances[$instanceName];
    }

    /**
     * @param \Monolog\Logger $logger
     * @param string $instanceName
     * @return $this
     */
    public function set(\Monolog\Logger $logger, $instanceName = self::SYSTEM)
    {
        if (!is_null($logger) && !is_null($instanceName)) {
            $this->instances[$instanceName] = $logger;
        }
        return $this;
    }

    /**
     * Configure given logger to default behaviour
     * @param \Monolog\Logger $logger
     * @param string $instanceName
     * @return $this
     */
    public function configureDefault(\Monolog\Logger $logger, $instanceName = self::SYSTEM)
    {
        switch ($instanceName) {
            case self::SYSTEM:
                $logger->pushHandler(new ErrorLogHandler());
                break;
            case self::ERROR:
                $handler = new NativeMailerHandler(
                    explode(',', \Config::paramsVariables('mail_support')),
                    '[Erreur] ' . $this->getRequest()->getServer(Request::SERVER_NAME),
                    \Config::paramsVariables('mail_robot'),
                    \Monolog\Logger::CRITICAL
                );
                $handler->setFormatter(new HtmlFormatter());
                $logger->pushProcessor(new IntrospectionProcessor());
                $logger->pushProcessor(new WebProcessor());
                $logger->pushHandler($handler);
                break;
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
