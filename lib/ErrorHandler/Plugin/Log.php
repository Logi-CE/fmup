<?php
namespace FMUP\ErrorHandler\Plugin;

class Log extends Abstraction
{
    public function canHandle()
    {
        return true;
    }

    public function handle()
    {
        error_log($this->getException());
        return $this;
    }
}
