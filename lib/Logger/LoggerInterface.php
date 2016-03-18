<?php
namespace FMUP\Logger;

use FMUP\Logger;

/**
 * Interface LoggerInterface
 * @package FMUP\Logger
 */
interface LoggerInterface
{
    /**
     * Define a logger to use
     * @param Logger $logger
     * @return $this
     */
    public function setLogger(Logger $logger);

    /**
     * Retrieve defined Logger
     * @return Logger
     * @throws Exception
     */
    public function getLogger();

    /**
     * Check if a logger has been defined
     * @return bool
     */
    public function hasLogger();
}
