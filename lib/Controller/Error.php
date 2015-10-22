<?php
namespace FMUP\Controller;

use FMUP\Controller;
use FMUP\Response\Header\Status;

/**
 * Class Error
 * @package FMUP\Controller
 */
abstract class Error extends Controller
{
    /**
     * @var \Exception
     */
    private $exception;

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
     * Url call for each Exception status
     */
    public function indexAction()
    {
        $e = $this->getException();
        if ($e instanceof \FMUP\Exception\Status) {
            $this->errorStatus($e->getStatus());
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
        $this->getResponse()->setHeader(new Status($status));
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
