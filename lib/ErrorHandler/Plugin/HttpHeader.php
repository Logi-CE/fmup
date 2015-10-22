<?php
namespace FMUP\ErrorHandler\Plugin;

use FMUP\Response\Header\Status;

class HttpHeader extends Abstraction
{
    public function canHandle()
    {
        return (!$this->getException() instanceof \FMUP\Exception\Status);
    }

    public function handle()
    {
        $this->getResponse()->setHeader(new Status(Status::VALUE_INTERNAL_SERVER_ERROR));
        return $this;
    }
}
