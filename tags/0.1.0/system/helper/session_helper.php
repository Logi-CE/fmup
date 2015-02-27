<?php
/*
 * Conservation des filtres etc
 */
class SessionHelper
{
    /*
     * Enregistrement des filtres, du paging et de l'ordre
     * @nom_filtre : le nom du filtre POSTÉ
     * @defaut_order : l'ordre par défaut
     */
    public static function setVariables ($nom_filtre)
    {
        SessionHelper::setFiltre($nom_filtre);
        SessionHelper::setPaging();
        SessionHelper::setOrdre();
    }

    /*
     * Enregistrement de tous les filtres en session
     * @nom_filtre : le nom du filtre POSTÉ
     */
    public static function setFiltre ($nom_filtre)
    {
        $page_courante = str_replace('lister', 'filtrer', $_GET['sys']);
        unset($_SESSION["classement"][$page_courante]["filtre"]);
        if (isset($_POST[$nom_filtre])) {
            $_SESSION["classement"][$page_courante]["filtre"] = $_POST[$nom_filtre];
        } else {
            // Sinon on vide les filtres
            //unset($_SESSION["classement"]["filtre"]);
        }
    }

    /*
     * Récupération des filtres
     * @nom_filtre : le nom du filtre POSTÉ
     * @valeur : le nom du champ à récupérer
     * @valeur_de_base : le champ s'il est vide
     */
    public static function getFiltre ($valeur, $valeur_de_base = "")
    {
        if (isset($_SESSION["classement"][$_GET['sys']]["filtre"][$valeur])) {
            $retour = $_SESSION["classement"][$_GET['sys']]["filtre"][$valeur];
        } else {
            $retour = $valeur_de_base;
        }
        return $retour;
    }

    /*
     * Enregistrement du paging
     */
    public static function setPaging ()
    {
        $page_courante = str_replace('lister', 'filtrer', $_GET['sys']);
        if (isset($_POST['page_courante'])) {
            $_SESSION["classement"][$page_courante]["page_courante"] = $_POST['page_courante'];
        } else {
            // Sinon on met la page à 1
            //unset($_SESSION["classement"]["page_courante"]);
        }
    }

    /*
     * Récupération du paging
     */
    public static function getPaging ()
    {
        if (isset($_SESSION["classement"][$_GET['sys']]["page_courante"])) {
            $retour = $_SESSION["classement"][$_GET['sys']]["page_courante"];
        } else {
            $retour = 1;
        }
        return $retour;
    }

    /*
     * Enregistrement de l'ordre
     */
    public static function setOrdre ()
    {
        $page_courante = str_replace('lister', 'filtrer', $_GET['sys']);
        if (isset($_POST['order_by'])) {
            $_SESSION["classement"][$page_courante]["order_by"] = $_POST['order_by'];
        //} elseif (!isset($_GET['from_retour']) && !isset($_POST['order_by'])) {
            // Sinon on met l'ordre par défaut
            //unset($_SESSION["classement"]["order_by"]);
        }
    }

    /*
     * Récupération de l'ordre
     * @valeur_de_base : le champ s'il est vide
     */
    public static function getOrdre ($valeur_de_base = '')
    {
        if (isset($_SESSION['classement'][$_GET['sys']]['order_by'])) {
            $retour = $_SESSION['classement'][$_GET['sys']]['order_by'];
        } else {
            $retour = $valeur_de_base;
        }
        return $retour;
    }

    /*
     * Remise à zéro de tous les filtres, du paging et de l'ordre
     */
    public static function vider ()
    {
        unset($_SESSION["classement"][$_GET['sys']]);
    }
}
