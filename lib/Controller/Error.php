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
    public function preFiltre($calledAction)
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
            $this->writeContextToLog();
        }
        $this->render();
    }

    abstract public function render();

    /**
     * Sends error message
     * @param string $status
     * @return $this
     */
    protected function errorStatus($status)
    {
        error_log($status);
        $this->writeContextToLog()
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
