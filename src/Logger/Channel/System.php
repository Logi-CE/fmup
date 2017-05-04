<?php
namespace FMUP\Logger\Channel;

use Monolog\Handler\ErrorLogHandler;
use FMUP\Logger\Channel;

class System extends Channel
{
    const NAME = 'System';

    public function configure()
    {
        $this->getLogger()->pushHandler(new ErrorLogHandler());
        return $this;
    }
}
