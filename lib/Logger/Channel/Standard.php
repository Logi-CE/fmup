<?php
namespace FMUP\Logger\Channel;

use FMUP\Environment;
use FMUP\Sapi;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\FirePHPHandler;

class Standard extends System
{
    const NAME = 'Standard';

    public function configure()
    {
        parent::configure();
        $canSendHeaders = !$this->headerSent() && $this->getSapi()->get() != Sapi::CLI;
        $isDev = $this->getEnvironment()->get() == Environment::DEV;
        $allowBrowser = isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Castelis') !== false;
        if ($canSendHeaders && ($allowBrowser || $isDev)) {
            $this->getLogger()
                ->pushHandler(new FirePHPHandler())
                ->pushHandler(new ChromePHPHandler());
        }
        return $this;
    }

    /**
     * @codeCoverageIgnore
     * @return bool
     */
    protected function headerSent()
    {
        return headers_sent();
    }
}
