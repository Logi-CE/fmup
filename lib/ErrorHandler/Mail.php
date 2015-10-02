<?php
namespace FMUP\ErrorHandler;

use FMUP\Response\Header\Status;

class Mail extends Abstraction
{
    public function canHandle()
    {
        return (!$this->getException() instanceof \FMUP\Exception\Status);
    }

    public function handle()
    {
        $this->writeContextToLog()
            ->sendMailOnException()
            ->getResponse()
            ->setHeader(new Status(Status::VALUE_INTERNAL_SERVER_ERROR));
    }

    /**
     * Will send a mail if useDailyAlert is not active and we're not in debug
     * @todo rewrite to avoid use of Error
     * @uses \Config
     * @uses \Error
     * @return self
     */
    protected function sendMailOnException()
    {
        if (
            !$this->getBootstrap()->getConfig()->get('use_daily_alert') &&
            !$this->getBootstrap()->getConfig()->get('is_debug')
        ) {
            try {
                throw new \Error($this->getException()->getMessage(), E_WARNING);
            } catch (\Exception $e) {
            }
        }
        return $this;
    }

    protected function writeContextToLog()
    {
        error_log($this->getException());
        return $this;
    }
}
