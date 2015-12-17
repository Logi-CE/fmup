<?php

/**
 * Classe permettant de formater des valeurs pour affichage
 * @version 1.0
 */
class UniteHelper
{

    /**
     * Retourne un décimal formaté en monnaie
     * @param float $valeur : La valeur à formater
     * @param string $monaie : [OPT] La monnaie à afficher, par défaut "&euro;"
     * @param int $virgule : [OPT] Le nombre de chiffres après la virgule, par défaut 2
     * @return string : Valeur formatée
     */
    public static function getFormatMonetaire($valeur, $format = '€', $virgule = 2)
    {
        return self::getNombreFormat($valeur, $virgule, $format);
    }

    /**
     * Retourne un décimal formaté
     * @param float $valeur : La valeur à formater
     * @param int $virgule : Le nombre de chiffres après la virgule
     * @param string $format : [OPT] Le format à afficher, par défaut rien
     * @param string $separateur : [OPT] Le séparateur de décimal, par défaut ","
     * @param string $separateur_millier : [OPT] Le séparateur de millier, par défaut " "
     * @return string : Valeur formatée
     */
    public static function getNombreFormat(
        $valeur,
        $virgule,
        $format = '',
        $separateur = ",",
        $separateur_millier = " "
    ) {
        if (!$valeur) {
            $valeur = 0;
        }
        $valeur_formatee = number_format(str_replace(",", ".", $valeur), $virgule, $separateur, $separateur_millier);
        if ($format) {
            $valeur_formatee .= ' ' . $format;
        }
        return $valeur_formatee;
    }

    public static function getInt($id = "")
    {
        $retour = 0;

        $id = str_replace(" ", "", $id);
        $id = str_replace(",", ".", $id);

        if (intVal($id) . "" == $id . "") {
            $retour = intVal($id);
        }

        return $retour;
    }
}
