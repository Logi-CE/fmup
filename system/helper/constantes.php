<?php
/**
 * Définit un jeu de constantes pour le site
 **/
class Constantes extends ConstantesApplication
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
        return "<strong>Le site est actuellement en maintenance.</strong><br />Veuillez nous excusez pour la gêne occasionnée.";
    }
    
    public static function getMessagePageNonTrouvee ()
    {
        return "Cette page n'existe pas.<br/>Vous allez être redirigé vers la page d'accueil.";
    }
    
    public static function getMessageAttenteFlash () {
        return '<img src="'.Constantes::getSrcImageLoader().'" alt="" />Traitement en cours...<br />Merci de patienter.';
    }

    /**
     * Message quand il n'y a pas d'éléments dans une ligne
     */
    public static function messageListeVide($colspan = 5)
    {
        return '<tr class="pas_de_resultats"><td colspan="'.$colspan.'">Pas de résultats</td></tr>';
    }
    /**
     * Paging
     * @return Nb d'éléments par page
     */
    public static function getNbMaxPage ()
    {
        // 08/01/10 : par défaut : 14, modification pour adaptation à une résolution de 1024*768
        return 10;
    }

    /**
     * Paging
     * @return Nb d'éléments par page
     */
    public static function getNbMaxPageGrosseListe ()
    {
        return 18;
    }

    /*
     * Retourne l'adresse email de contact du site
     */
    public static function emailInfo()
    {
        return "mailto:'shuet@castelis.com'";
    }
    /*
     * Retourne l'adresse email de contact du site
     */
    public static function siteWeb()
    {
        return '';
    }
    /**
     * Message indiquant un nombre maximum de caractères autorisés
     */
    public static function messageNbMaxCaracteres($nb_max)
    {
        return $nb_max." caractères maximum";
    }

    /**
     * nombre de caractères maximum affichés dans les colonnes des listes
     */
    public static function getNbCaracteresMax()
    {
        return 20;
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

    /* **********************************
    * logue des changement dans la BDD *
    ********************************** */
    public static function getTablesALoguer()
    {
        return array_map(create_function('$i', 'return String::toCamlCase($i);'), array(
            'Documents',
            'utilisateur'
        ));
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
    public static function getTableauminutes ()
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
}
