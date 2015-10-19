<?php
/**
 * Fonctions validant l'accès au site
 * @version 1.0
 */
class DroitHelper
{
    /**
     * Fonction permettant l'accès au controleur et à la fonction demandée
     * Elle va laisser passer l'utilisateur pour certaines fonctions dites "d'accès libre" et à l'identification
     * L'utilisateur CASTELIS aura aussi le droit d'accès à toutes les pages
     * Elle est utilisée par la gestion de droits du menu (en lecture) et redirigera vers la page d'accueil ou d'identification si besoin
     * @param string $sys_controller : Le nom du controleur
     * @param string $sys_function : Le nom de la fonction
     * @param string $sys_directory : Le nom du dossier
     */
    public static function authorizeRead($sys_controller, $sys_function, $sys_directory)
    {
        if ($sys_controller == call_user_func(array(APP, 'getControllerIdentification'))) {
            // Toutes les fonctions du controlleur d'identification sont autorisées à tous.
        } else {
            if (!isset($_SESSION['id_utilisateur'])) {
                Controller::setFlash(Constantes::getMessageConnexionNecessaire());
                $_SESSION['get_initial'] = $_GET;
                Controller::redirect(call_user_func(array(APP, "authController")));
            } elseif (in_array($sys_controller, call_user_func(array(APP, "getListControllerAccesLibre")))) {
                // Tout le monde à le droit à ces controlleurs une fois connecté
            } elseif ($sys_controller == 'ctrl_console' && Config::consoleActive()) {
                // La console
            } else {
                $droits = Utilisateur::getUtilisateurConnecte()->getDroits($sys_controller, 'lecture');
                // L'utilisateur CASTELIS n'a pas besoin de droits d'accès
                if (!$droits && Utilisateur::getUtilisateurConnecte()->getId() != Config::paramsVariables('id_castelis')) {
                    Controller::setFlash(Constantes::getMessageDroitsInsuffisants());
                    Controller::redirect(call_user_func(array(APP, "defaultController")));
                }
            }
        }
    }
    
    /**
     * Fonction permettant l'accès au controleur et à la fonction demandée
     * L'utilisateur CASTELIS aura aussi le droit d'accès à toutes les pages
     * Elle est utilisée par la gestion de droits en écriture (doit donc être appelée manuellement dans le controleur) et redirigera vers la page d'accueil si besoin
     * @param string $sys_controller : Le nom du controleur
     * @param string $sys_function : Le nom de la fonction
     * @param string $sys_directory : Le nom du dossier
     */
    public static function authorizeWrite ($sys_controller, $sys_function = '', $sys_directory = '')
    {
        $droits = Utilisateur::getUtilisateurConnecte()->getDroits($sys_controller, 'ecriture');
        // L'utilisateur CASTELIS n'a pas besoin des droits d'écriture
        if (!$droits && Utilisateur::getUtilisateurConnecte()->getId() != Config::paramsVariables('id_castelis')) {
            Controller::setFlash(Constantes::getMessageDroitsInsuffisants());
            Controller::redirect(call_user_func(array(APP, "defaultController")));
        }
    }
}
