<?php
namespace FMUP\Logger\Channel;

use FMUP\Environment;
use FMUP\Sapi;
use FMUP\Logger\Channel;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\ChromePHPHandler;

class Standard extends Channel
{
    const NAME = 'Standard';
    private $sapi;

    public function getName()
    {
        return self::NAME;
    }

    public function configure()
    {
        if (
            $this->getEnvironment()->get() == Environment::DEV
            && !headers_sent()
            && $this->getSapi()->get() != Sapi::CLI
        ) {
            $this->getLogger()
                ->pushHandler(new FirePHPHandler())
                ->pushHandler(new ChromePHPHandler());
        }
        return $this;
    }

    /**
     * get Sapi instance
     * @return Sapi
     */
    public function getSapi()
    {
        if (!$this->sapi) {
            $this->sapi = Sapi::getInstance();
        }
        return $this->sapi;
    }

    /**
     * @param Sapi $sapi
     * @return $this
     */
    public function setSapi(Sapi $sapi)
    {
        $this->sapi = $sapi;
        return $this;
    }
}
