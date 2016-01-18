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

    /**
     * @return string
     */
    protected function getLoggerName()
    {
        return Logger\Channel\Standard::NAME;
    }

    /**
     * Log message if message is defined
     * @param int $level
     * @param string $message
     * @param array $context
     * @return $this
     * @throws Exception
     */
    public function log($level, $message, array $context = array())
    {
        if ($this->hasLogger()) {
            $context['class_origin'] = get_called_class();
            $this->getLogger()->log($this->getLoggerName(), (int)$level, $message, (array)$context);
        }
        return $this;
    }
}
