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
            throw $this->exception;
        } catch (\FMUP\Exception\Status $e) {
            $this->error($e->getStatus());
        } catch (\Exception $e) {
            throw $e; //uncaught exception because we don't know
        }
        ob_start();
        new \View('accueil/erreur404', array('fil_ariane' => 'Accueil > Erreur', 'error' => $this->exception->getMessage()));
        $view = ob_get_clean();

        $this->getResponse()->setBody($view);
    }

    private function error($status)
    {
        error_log($status);
        \FMUP\Error::addContextToErrorLog();

        $this->getResponse()->addHeader(Status::TYPE, $status);
    }
}
