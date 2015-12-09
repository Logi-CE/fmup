<?php
/**
 * Définit un jeu de constantes pour le site
 * @version 1.0
 */
class Constantes
{
    public static function getMessageErreurApplication ()
    {
        $retour = "<br/>Une erreur est survenue !<br/>";
        $retour .= "Le support informatique a été prévenu ";
        $retour .= "et règlera le problème dans les plus brefs délais.<br/>";
        $retour .= "<br/>";
        $retour .= "L'équipe des développeurs vous prie de l'excuser pour le désagrément.<br/>";

        return $retour;
    }
    
    public static function getMessageFlashMaintenance ()
    {
        return "Une maintenance est en cours sur le site.<br />Merci de vous déconnecter dès que possible";
    }
    
    public static function getMessageConnexionMaintenance ()
    {
        return "<strong>Le site est actuellement en maintenance.</strong>"
        ."<br />Veuillez nous excusez pour la gêne occasionnée.";
    }
    
    public static function getMessagePageNonTrouvee ()
    {
        return "Cette page n'existe pas.<br/>Vous allez être redirigé vers la page d'accueil.";
    }
    
    public static function getMessageAttenteFlash () {
        return '<img src="'.self::getSrcImageLoader().'" alt="" />Traitement en cours...<br />Merci de patienter.';
    }

    /**
     * Message affiché quand il n'y a pas d'éléments dans une ligne
     * @return string : Le message
     */
    public static function messageListeVide ()
    {
        return 'Pas de résultats';
    }
    
    public static function getMessageFlashInsertionOk ()
    {
        return "Création réalisée avec succès.";
    }
    public static function getMessageFlashModificationOk ()
    {
        return "Mise à jour réalisée avec succès.";
    }
    public static function getMessageFlashSuppressionOk ()
    {
        return "Suppression réalisée avec succès.";
    }
    public static function getMessageFlashEnregistrementDocumentOk ()
    {
        return "Enregistrement du document réalisé avec succès.";
    }
    public static function getMessageFlashErreurEnregistrement ()
    {
        return "Erreur pendant l'enregistrement";
    }
    public static function getMessageFlashErreurSuppression ()
    {
        return "Erreur pendant la suppression";
    }
    public static function getMessageFlashBlocageSuppression ()
    {
        return "Suppression non autorisée";
    }
    /**
     * Message présent dans les fonctions de droits, indiquant que l'utilisateur n'a pas les droits suffisants
     * @return string : Le message
     */
     public static function getMessageDroitsInsuffisants ()
    {
        return "Vous n'avez pas les droits suffisants pour accéder à cette page.";
    }
    /**
     * Message présent dans les fonctions de droits, indiquant que l'utilisateur doit se connecter
     * @return string : Le message
     */
    public static function getMessageConnexionNecessaire ()
    {
        return "Vous devez vous connecter avant d'accèder à cette zone";
    }
    
    public static function getMessageMailOubliEnvoye ()
    {
        return 'Nous avons bien pris en compte votre demande.'
        .' Vous allez recevoir un mail de réinitialisation.::Réinitialisation::ok';
    }
    
    public static function getMessageDeconnexion ()
    {
        return "Vous avez bien été déconnecté.::Déconnexion::ok";
    }
    
    public static function getMessageErreurConnexion ()
    {
        return "Erreur lors de l'authentification::Erreur::alerte";
    }
    
    public static function imageOui ()
    {
        return '<span class="fa fa-check-circle fa-lg" style="color: limegreen;"></span>';
    }
    public static function imageNon ()
    {
        return '<span class="fa fa-minus-circle fa-lg" style="color: red;"></span>';
    }

    /**************
    *   filtres   *
    * ************/

    public static function tableauFiltreOuiNon ($defaut = '***')
    {
        return array(
            '' 	=> $defaut,
            '1' => 'Oui',
            '0' => 'Non'
        );
    }

    /**
    * Tableaux pour les heures et les minutes
    */
    public static function getTableauHeures($format_24h = true)
    {
        $tableau = array();
        for ($h = 0; $h < 12 + (12 * $format_24h); $h++) {
            $tableau[] = substr('0'.$h, -2);
        }
        return $tableau;
    }
    public static function getTableauMinutes ()
    {
        $tableau = array();
        for ($m = 0; $m < 60; $m++) {
            $tableau[] = substr('0'.$m, -2);
        }
        return $tableau;
    }

    /*
     * Civilite
     */
    public static function getCiviliteMme()
    {
        return 1;
    }
    public static function getCiviliteMlle()
    {
        return 2;
    }
    public static function getCiviliteMr()
    {
        return 3;
    }
    
    public static function getNombreMinutesExpirationMotPasse ()
    {
        return 30;
    }
    
    public static function getNombreCaractereMinimumMotPasse ()
    {
        return 6;
    }
    
    /**
     * Durée en minutes avant de déclarer le blocage du jeton utilisé
     */
    public static function getDureeAvantBlocageCron ()
    {
        return 60;
    }
    
    public static function getEtatCronLibre ()
    {
        return 1;
    }
    
    public static function getEtatCronOccupe ()
    {
        return 2;
    }
    
    public static function getEtatCronBloque ()
    {
        return 3;
    }
    
    public static function getEtatCronBloqueIndique ()
    {
        return 4;
    }
}
