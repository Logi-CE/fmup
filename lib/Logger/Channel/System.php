<?php
namespace FMUP\Logger\Channel;

use Monolog\Handler\ErrorLogHandler;

class System extends Syslog
{
    const NAME = 'System';

    public function configure()
    {
        parent::configure();
        $this->getLogger()->pushHandler(new ErrorLogHandler());
        return $this;
    }
}
