<?php
namespace FMUP\Logger\Channel;

use Monolog\Handler\ErrorLogHandler;

class System extends Standard
{
    const NAME = 'System';

    public function getName()
    {
        return self::NAME;
    }

    public function configure()
    {
        parent::configure();
        $this->getLogger()->pushHandler(new ErrorLogHandler());
        return $this;
    }
}
