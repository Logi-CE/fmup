<?php
namespace FMUP\Logger;

use FMUP\Logger;

trait LoggerTrait
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Logger $logger
     * @return $this
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return Logger
     * @throws Exception
     */
    public function getLogger()
    {
        if (!$this->hasLogger()) {
            throw new Exception('Logger must be defined');
        }
        return $this->logger;
    }

    /**
     * @return bool
     */
    public function hasLogger()
    {
        return (bool) $this->logger;
    }
}
