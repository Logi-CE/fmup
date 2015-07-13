<?php
/**
 * Classe d'erreur pour les pages non trouvés
 * @author afalaise
 * @version 1.0
 * @deprecated use \FMUP\Exception\Status\NotFound instead
 * @see \FMUP\Exception\Status\NotFound
 */
class NotFoundError extends Error
{
    public function __construct($message)
    {
        $this->message = $message;

        // On génère une alerte par précaution
        parent::__construct($message, E_WARNING);

        header('HTTP/1.0 404 Not Found');

        // Si on est sur la page, on ne redirige plus
        if ($_SERVER['REQUEST_URI'] != call_user_func(array(APP, "page404"))) {
            Controller::redirect(call_user_func(array(APP, "page404")));
        } else {
            throw new Error('La page 404 est introuvable.');
        }
    }
}
