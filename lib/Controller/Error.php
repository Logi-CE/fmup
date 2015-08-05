<?php
namespace FMUP\Controller;

use FMUP\Response\Header\Status;

/**
 * Class Error
 * @package FMUP\Controller
 */
abstract class Error extends \FMUP\Controller
{
    /**
     * @var \Exception
     */
    private $exception;

    /**
     * rewrite to tell everybody can access error controller
     * @param string $calledAction
     */
    public function preFilter($calledAction = null)
    {
    }

    /**
     * Define exception
     * @param \Exception $exception
     * @return $this
     */
    public function setException(\Exception $exception)
    {
        $this->exception = $exception;
        return $this;
    }

    /**
     * Url call for each 404
     */
    public function indexAction()
    {
        $e = $this->getException();
        if ($e instanceof \FMUP\Exception\Status) {
            $this->errorStatus($e->getStatus());
        } else {
            $this->writeContextToLog()
                ->sendMailOnException()
                ->getResponse()
                ->setHeader(new Status(Status::VALUE_INTERNAL_SERVER_ERROR));
        }
        $this->render();
    }

    abstract public function render();

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

    /**
     * Sends error message
     * @param string $status
     * @return $this
     */
    protected function errorStatus($status)
    {
        error_log($status);
        $this->writeContextToLog()
            ->sendMailOnException()
            ->getResponse()
            ->setHeader(new Status($status));
        return $this;
    }

    protected function writeContextToLog()
    {
        \FMUP\Error::addContextToErrorLog();
        return $this;
    }

    /**
     * @return \Exception
     */
    protected function getException()
    {
        return $this->exception;
    }
}
