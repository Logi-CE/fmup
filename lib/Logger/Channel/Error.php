<?php
namespace FMUP\Logger\Channel;

use FMUP\Request;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\WebProcessor;
use Monolog\Logger;

/**
 * Class Error
 * @package FMUP\Logger\Channel
 */
class Error extends Standard
{
    const NAME = 'Error';

    private $request;

    public function getName()
    {
        return self::NAME;
    }

    public function configure()
    {
        parent::configure();
        $handler = new NativeMailerHandler(
            explode(',', $this->getConfig()->get('mail_support')),
            '[Erreur] ' . $this->getRequest()->getServer(Request::SERVER_NAME),
            $this->getConfig()->get('mail_robot'),
            Logger::CRITICAL
        );
        $handler->setFormatter(new HtmlFormatter());

        $this->getLogger()
            ->pushProcessor(new IntrospectionProcessor())
            ->pushProcessor(new WebProcessor())
            ->pushHandler($handler);
        return $this;
    }

    public function getRequest()
    {
        if (!$this->request) {
            $this->request = new Request();
        }
        return $this->request;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }
}
