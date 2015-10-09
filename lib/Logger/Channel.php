<?php
namespace FMUP\Logger;


abstract class Channel
{
    public function getLogger()
    {
        if (!isset($this->instances[$instanceName])) {
            $logger = new \Monolog\Logger($instanceName);
            $this->configureDefault($logger, $instanceName);
            $this->instances[$instanceName] = $logger;
        }
        return $this->instances[$instanceName];
    }
}
