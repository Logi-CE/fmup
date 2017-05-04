<?php
namespace FMUP\Logger\Channel;

use FMUP\Logger\Channel;
use Monolog\Handler\SyslogHandler;

class Syslog extends Channel
{
    const NAME = 'Syslog';

    private $identifier = 'webapp';

    /**
     * Name of syslog application (identifier)
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Define identifier for syslog
     * @param string $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = (string)$identifier;
        return $this;
    }

    /**
     * @return $this
     */
    public function configure()
    {
        $this->getLogger()->pushHandler(new SyslogHandler($this->getIdentifier()));
        return $this;
    }
}
