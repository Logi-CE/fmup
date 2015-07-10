<?php
/**
 * Classe permettant de formater des valeurs pour affichage
 * @version 1.0
 */
class UniteHelper
{
    /**
     * Affiche un texte au singulier ou au pluriel
     * @param int $valeur : La quantité
     * @param string $singulier : Le mot au singulier
     * @param string $pluriel : [OPT] Le mot au pluriel, par défaut il mettra un S à la fin
     * @param bool $afficher_valeur : [OPT] Faut il afficher la valeur devant le texte, par défaut oui
     * @return string : La quantité + le mot accordé
     */
    public static function getSingulierPluriel($valeur, $singulier, $pluriel = null, $afficher_valeur = true)
    {
        $retour = '';
        if ($afficher_valeur) {
            $retour = $valeur.' ';
        }
        
        if ($valeur <= 1) {
            $retour .= $singulier;
        } else {
            if (!$pluriel) {
                $pluriel = $singulier.'s';
            }
            $retour .= $pluriel;
        }
        
        return $retour;
    }
    
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
     * Retourne un décimal formaté en tonne
     * @param float $valeur : La valeur en tonne
     * @param int $virgule : [OPT] Le nombre de chiffres après la virgule, par défaut 3
     * @param string $format : [OPT] Le format à afficher, par défaut "t"
     * @return string : Valeur formatée
     */
    public static function getFormatPourcentage($valeur, $virgule = "1", $format = '%')
    {
        return self::getNombreFormat($valeur, $virgule, $format);
    }
    
    /**
     * Retourne un décimal formaté en tonne
     * @param float $valeur : La valeur en tonne
     * @param int $virgule : [OPT] Le nombre de chiffres après la virgule, par défaut 3
     * @param string $format : [OPT] Le format à afficher, par défaut "t"
     * @return string : Valeur formatée
     */
    public static function getFormatTonne($valeur, $virgule = "3", $format = 't')
    {
        return self::getNombreFormat($valeur, $virgule, $format);
    }
    
    /**
     * Retourne un décimal formaté en kilogrammes
     * @param float $valeur : La valeur en kilogrammes
     * @param int $virgule : [OPT] Le nombre de chiffres après la virgule, par défaut 3
     * @param string $format : [OPT] Le format à afficher, par défaut "kg"
     * @return string : Valeur formatée
     */
    public static function getFormatKilogramme($valeur, $virgule = "3", $format = 'kg')
    {
        return self::getNombreFormat($valeur, $virgule, $format);
    }

    /**
     * Retourne un décimal formaté en kilomètre
     * @param float $valeur : La valeur en kilomètre
     * @param int $virgule : [OPT] Le nombre de chiffres après la virgule, par défaut 2
     * @param string $format : [OPT] Le format à afficher, par défaut "km"
     * @return string : Valeur formatée
     */
    public static function getFormatKilometre($valeur, $virgule = "2", $format = 'km')
    {
        return self::getNombreFormat($valeur, $virgule, $format);
    }

    /**
     * Retourne un décimal formaté en mètre carré
     * @param float $valeur : La valeur en mètre carré
     * @param int $virgule : [OPT] Le nombre de chiffres après la virgule, par défaut 2
     * @param string $format : [OPT] Le format à afficher, par défaut "m²"
     * @return string : Valeur formatée
     */
    public static function getFormatMetreCarre($valeur, $virgule = "2", $format = 'm²')
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
    public static function getNombreFormat ($valeur, $virgule, $format = '', $separateur = ",", $separateur_millier = " ")
    {
        if (!$valeur) {
            $valeur = 0;
        }
        $valeur_formatee = number_format(str_replace(".", ",", $valeur), $virgule, $separateur, $separateur_millier);
        if ($format) {
            $valeur_formatee .= ' '.$format ;
        }
        return $valeur_formatee;
    }

    /**
     * Formate un nombre pour excel, il n'aura pas d'unité
     * @param float $valeur : Le nombre à formater
     * @param string $virgule : [OPT] Le nombre de chiffres après la virgule, par défaut 2
     * @return string : Valeur formatée
     */
    public static function getFormatNombreExcel($valeur, $virgule = "2")
    {
        return UniteHelper::getNombreFormat($valeur, $virgule, '', ',', '');
    }

    public static function getInt($id="") {
        $retour = 0;        
        
        $id = str_replace(" ", "", $id);
        $id = str_replace(",", ".", $id);
        
        if (intVal($id)."" == $id."") {
            $retour = intVal($id);
        }
    
        return $retour;
    }
}