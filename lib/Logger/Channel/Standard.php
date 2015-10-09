<?php
namespace FMUP\Logger\Channel;

use FMUP\Logger\Channel;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\ChromePHPHandler;

class Standard extends Channel
{
    const NAME = 'Standard';

    public function getName()
    {
        return self::NAME;
    }

    public function configure()
    {
        if ($this->getConfig()->get('version') == 'dev') { //@todo something clean
            $this->getLogger()
                ->pushHandler(new FirePHPHandler())
                ->pushHandler(new ChromePHPHandler());
        }
        return $this;
    }
}
