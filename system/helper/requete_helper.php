<?php
class RequeteHelper
{
    /**
    * Cette fonction doit créer un where pour les utilisateurs en fonction des arguments suivants :
    * 		$champs qui est un tableau des champs à mettre dans la requête
    * 		$condition qui sera soit "AND" soit "OR", pour laisser le choix de la requete
    * 		$id_user qui est l'id utilisateur qui sera au centre de la requête
    * 		$parenté qui va determiner si on recupère les fils, les pères, les grands-pères etc
    **/
    /* MEMO
     *   parenté :
     *   0 : lui
     *   1 : + fils
     *   2 : + parents
     *   3 : + fils + parents
     *   4 : + fils + petits-fils
     *   5 : + parents + grands-parents
     *   6 : + fils + petits-fils + parents + grands-parents
    */
    public static function droitUtilisateurWhere($champs, $condition, $id_user, $parente = 0)
    {
        $requete = array();
        $utilisateur = Utilisateur::findOne(Sql::secureId($id_user));
        $liste_user = array();
        $liste = "";
        // Si l'user n'est égal à rien, on prendra l'id_session
        if (!$utilisateur) {
            $utilisateur = Utilisateur::getUtilisateurSession();
        }
        // On prépare la liste
        switch ($parente) {
            case Constantes::getIdParenteUserEtFils():
                $liste_user = $utilisateur->getFils();
                $liste_user[] = $utilisateur;
                break;
            case Constantes::getIdParenteUserEtPere():
                $liste_user = $utilisateur->getParents();
                $liste_user[] = $utilisateur;
                break;
            case Constantes::getIdParenteUserEtFilsEtPere():
                $liste_user = $utilisateur->getPereEtFils();
                $liste_user[] = $utilisateur;
                break;
            case Constantes::getIdParenteUserEtFilsEtPlus():
                $liste_user = $utilisateur->getListeToutesPersonnesEnDessous();
                break;
            case Constantes::getIdParenteUserEtPereEtPlus():
                $liste_user = $utilisateur->getListePlusHautsResponsables();
                break;
            case Constantes::getIdParenteUserEtDescendance():
                // Pas très propre (id_utilisateur présent deux fois)
                $liste_user = $utilisateur->getListeToutesPersonnesEnDessous();
                $liste_user = $utilisateur->getListePlusHautsResponsables($liste_user);
                break;
            // Tout le reste est considéré comme user seul
            default:
                $liste_user[] = $utilisateur;
                break;
        }
        // Et on en extrait les IDs, dans une chaine
        foreach ($liste_user as $user) {
            if ($user) {
                $liste .= $user->getId().",";
            }
        }
        $liste = substr($liste, 0, -2);

        // Décomposition des champs en parties de requête
        if ($condition == "AND") {
            $requete = "(1=1";
            foreach ($champs as $champ) {
                $requete .= " AND ".$champ." IN (".$liste.")";
            }
            $requete .= ")";
        } elseif ($condition == "OR") {
            $requete = "(1=0";
            foreach ($champs as $champ) {
                $requete .= " OR ".$champ." IN (".$liste.")";
            }
            $requete .= ")";
        } else {
            die ("Condition incorrecte.");
        }

        return $requete;
    }

    public static function convertFiltreDate($key, $value)
    {
        if (!$value) {
            return '';
        } elseif (Is::date($value)) {
            //Si la date est une date le LIKE est inutile
            //return  $key." LIKE '%".Date::frToUkMysql($value)."%'";
            return $key." = '".Date::frToUkMysql($value)."'";
        } elseif (preg_match('#^(\d{2})/$#', $value, $date)) {
            return $key." LIKE '%-".$date[1]."'";
        } elseif (preg_match('#^(\d{2})/(\d{2})$#', $value, $date)) {
            return $key." LIKE '%".$date[2]."-".$date[1]."'";
        } elseif (preg_match('#^/(\d{2})$#', $value, $date)) {
            return $key." LIKE '%-".$date[1]."-%'";
        } else {
            return $key." LIKE '%$value%'";
        }
    }

    public static function getListeId ($liste_objet)
    {
        $retour = "";
        foreach ($liste_objet as $objet) {
            $retour .= $objet->getId().",";
        }
        return substr($retour, 0, -1);
    }
}
