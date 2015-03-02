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
     * rewrite to tell everybody can access error controller
     */
    public function preFiltre()
    {
    }

    /**
     * Url call for each 404
     */
    public function indexAction()
    {
        ob_start();
        new \View('accueil/erreur404', array('fil_ariane' => 'Accueil > Erreur'));
        $view = ob_get_clean();

        error_log('404 Not Found');
        \FMUP\Error::addContextToErrorLog();

        $this->getResponse()
                ->addHeader(Status::TYPE, Status::VALUE_NOT_FOUND)
                ->setBody($view)
        ;
    }
}
