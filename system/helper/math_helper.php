<?php
/*
 * Fonctions de calculs mathématiques
 */
class MathHelper
{
    /**
     * Calcul de la moyenne entre deux valeurs (valeur1 par rapport à valeur2)
     * @param {valeur1} : La première valeur
     * @param {valeur2} : La seconde valeur
     * @param {nb_chiffres_arrondi} : Le nombre de chiffres pour l'arrondi (par défaut 2 chiffres après la virgule)
     * @return La moyenne entre ces deux valeurs arrondie à 2 chiffres après la virgule
     */
    public static function getMoyenne($valeur1, $valeur2, $nb_chiffres_arrondi = 2)
    {
        $moyenne = ($valeur2 != 0) ? $valeur1 / $valeur2 : 0;
        return number_format($moyenne, $nb_chiffres_arrondi);
    }
     /**
      * Calcul du pourcentage d'évolution entre deux valeurs (évolution de valeur2 par rapport à valeur1)
      * @param {valeur1} : La première valeur
      * @param {valeur2) : La seconde valeur
      * @param {format_pourcentage} : Indique si on retourne le résultat formatté en pourcentage via le getFormatPourcentage de UniteHelper ou non (false par défaut)
      * @param {nb_chiffres_arrondi} : Le nombre de chiffres arrondis (uniquement pris en compte si on ne renvoit pas le résultat formatté en pourcentage)
      */
    public static function getPourcentageEvolution($valeur1, $valeur2, $format_pourcentage = false, $nb_chiffres_arrondi = 2)
    {
        $evolution = 0;
        if ($valeur2 == 0) {
            $evolution = ($valeur1 == 0) ? 0 : 100;
        } else {
            $evolution = ($valeur1 == 0) ? -100 : (($valeur1 - $valeur2) * 100 / $valeur2);
        }
        if ($format_pourcentage) {
            return UniteHelper::getFormatPourcentage($evolution);
        }
        return $evolution;
    }
}
