<?php
namespace FMUP\Logger;

use FMUP\Logger;

interface LoggerInterface
{
    /**
     * @param Logger $logger
     * @return $this
     */
    public function setLogger(Logger $logger);

    /**
     * @return Logger
     * @throws Exception
     */
    public function getLogger();

    /**
     * @return bool
     */
    public function hasLogger();
}
