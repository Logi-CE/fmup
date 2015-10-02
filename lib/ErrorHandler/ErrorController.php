<?php
namespace FMUP\ErrorHandler;

/**
 * Class ErrorController
 * @package FMUP\ErrorHandler
 */
class ErrorController extends Abstraction
{
    private $errorController;

    /**
     * @param \FMUP\Controller\Error $errorController
     */
    public function __construct(\FMUP\Controller\Error $errorController)
    {
        $this->setErrorController($errorController);
    }

    /**
     * @param \FMUP\Controller\Error $errorController
     * @return $this
     */
    public function setErrorController(\FMUP\Controller\Error $errorController)
    {
        $this->errorController = $errorController;
        return $this;
    }

    /**
     * @return \FMUP\Controller\Error
     * @throws \FMUP\Exception
     */
    public function getErrorController()
    {
        if (!$this->errorController) {
            throw new \FMUP\Exception('Error Controller must be set');
        }
        return $this->errorController;
    }

    /**
     * Always handle error with error controller
     * @return bool
     */
    public function canHandle()
    {
        return true;
    }

    /**
     * @return $this
     * @throws \FMUP\Exception
     */
    public function handle()
    {
        $this->getErrorController()
            ->setBootstrap($this->getBootstrap())
            ->setRequest($this->getRequest())
            ->setResponse($this->getResponse())
            ->setException($this->getException())
            ->indexAction();
        return $this;
    }
}
