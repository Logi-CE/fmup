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
        if (
            $this->getEnvironment()->get() == Environment::DEV
            && !headers_sent()
            && !$this->getSapi()->is(Sapi::CLI)
        ) {
            $this->getLogger()
                ->pushHandler(new FirePHPHandler())
                ->pushHandler(new ChromePHPHandler());
        }
        return $this;
    }
}
