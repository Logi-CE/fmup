<?php
namespace FMUP\Logger\Channel;

use FMUP\Environment;
use FMUP\Sapi;
use FMUP\Logger\Channel;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\ChromePHPHandler;

class Standard extends Channel
{
    use Sapi\OptionalTrait;

    const NAME = 'Standard';

    public function getName()
    {
        return self::NAME;
    }

    public function configure()
    {
        $canSendHeaders = !headers_sent() && $this->getSapi()->get() != Sapi::CLI;
        $isDev = $this->getEnvironment()->get() == Environment::DEV;
        $allowBrowser = isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Castelis') !== false;
        if ($canSendHeaders && ($allowBrowser || $isDev)) {
            $this->getLogger()
                ->pushHandler(new FirePHPHandler())
                ->pushHandler(new ChromePHPHandler());
        }
        return $this;
    }
}
