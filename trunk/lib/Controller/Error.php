<?php
namespace FMUP\Controller;

use FMUP\Response\Header\Status;

/**
 * Class Error
 * @package FMUP\Controller
 */
class Error extends \FMUP\Controller
{
    /**
     * @var \Exception
     */
    private $exception;

    /**
     * rewrite to tell everybody can access error controller
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
        try {
            throw $this->getException();
        } catch (\FMUP\Exception\Status $e) {
            $this->errorStatus($e->getStatus());
        } catch (\Exception $e) {
            throw $e; //uncaught exception because we don't know
        }
        ob_start();
        new \View('accueil/erreur404', array('fil_ariane' => 'Accueil > Erreur', 'error' => $this->getException()->getMessage()));
        $view = ob_get_clean();

        $this->getResponse()->setBody($view);
    }

    /**
     * Sends error message
     * @param string $status
     */
    protected function errorStatus($status)
    {
        error_log($status);
        \FMUP\Error::addContextToErrorLog();

        $this->getResponse()->setHeader(new Status($status));
    }

    /**
     * @return \Exception
     */
    protected function getException()
    {
        return $this->exception;
    }
}
