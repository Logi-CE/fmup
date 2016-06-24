<?php
namespace FMUP\ErrorHandler\Plugin;

use FMUP\Controller\Error;
use FMUP\Exception;

/**
 * Class ErrorController
 * @package FMUP\ErrorHandler
 */
class ErrorController extends Abstraction
{
    private $errorController;

    /**
     * @param Error $errorController
     */
    public function __construct(Error $errorController)
    {
        $this->setErrorController($errorController);
    }

    /**
     * @param Error $errorController
     * @return $this
     */
    public function setErrorController(Error $errorController)
    {
        $this->errorController = $errorController;
        return $this;
    }

    /**
     * @return Error
     * @throws Exception
     */
    public function getErrorController()
    {
        if (!$this->errorController) {
            throw new Exception('Error Controller must be set');
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
        $errorController = $this->getErrorController();
        $errorController
            ->setBootstrap($this->getBootstrap())
            ->setRequest($this->getRequest())
            ->setResponse($this->getResponse())
            ->setException($this->getException());
        $errorController->preFilter('index');
        $errorController->indexAction();
        $errorController->postFilter('index');
        return $this;
    }
}
