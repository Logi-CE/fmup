<?php
namespace FMUP\Logger\Channel;

use FMUP\Environment;
use FMUP\Logger\Channel;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\ChromePHPHandler;

class Standard extends Channel
{
    const NAME = 'Standard';
    const CLI_SAPI = 'cli';

    public function getName()
    {
        return self::NAME;
    }

    public function configure()
    {
        if (
            $this->getEnvironment()->get() == Environment::DEV
            && !headers_sent()
            && strtolower(substr(PHP_SAPI, 0, 3)) != self::CLI_SAPI
        ) {
            $this->getLogger()
                ->pushHandler(new FirePHPHandler())
                ->pushHandler(new ChromePHPHandler());
        }
        return $this;
    }
}
