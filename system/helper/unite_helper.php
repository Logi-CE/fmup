<?php
class UniteHelper
{
    /**
     * Retourne un décimal formaté en monaie
     *
     * @param {Integer} la valeur
     * @param {String} la monaie à afficher -> par défaut "&euro;"
     * @param {Integer} le nombre de chiffres après la virgule
     **/
    public static function getFormatMonetaire($valeur, $monaie = '€', $virgule = 2)
    {
        if (Is::decimal($valeur)) {
        //if ($valeur) {
            $valeur_formatee = number_format($valeur, $virgule, ',', ' ') ;
            if ($monaie) {
                $valeur_formatee.= ' '.$monaie ;
            }
            return $valeur_formatee;
        } else {
            return false;
        }

    }

    public static function getFormatPourcentage($valeur, $virgule = 1)
    {
        $valeur_formatee = number_format($valeur, $virgule, ',', ' ').' %' ;
        return $valeur_formatee;
    }

    /**
     * Affiche un texte au singulier ou au pluriel
     * @param {Integer|Float|String} $valeur            La quantité
     * @param {String}               $singulier         Le mot au singulier
     * @param {String}               $pluriel           Le mot au pluriel (facultatif)
     * @param {Boolean}              $afficher_valeur   Faut il afficher la valeur devant le texte
     * @return {String}                                 La quantité + le mot accordé
     **/
    public static function getSingulierPluriel($valeur, $singulier, $pluriel = null, $afficher_valeur = true)
    {
        if (!$pluriel) {
            $pluriel = $singulier.'s';
        }
        if ($valeur <= 1) {
            if ($afficher_valeur) {
                return "$valeur $singulier";
            } else {
                return "$singulier";
            }
        } else {
            if ($afficher_valeur) {
                return "$valeur $pluriel";
            } else {
                return "$pluriel";
            }
        }
    }

    /**
     * Retourne un décimal formaté en tonne
     *
     * @param {Integer} la valeur
     * @param {Integer} le nombre de chiffres après la virgule
     **/
    public static function getFormatTonne($valeur, $virgule = "3", $format = 't')
    {
        $valeur_formatee = number_format($valeur, $virgule, ',', ' ');
        if ($format) {
            $valeur_formatee.= ' '.$format ;
        }
        return $valeur_formatee;
    }

    public static function getFormatKilometre($valeur, $virgule = "2", $format = 'km')
    {
        $valeur_formatee = number_format($valeur, $virgule, ',', ' ') ;
        if ($format) {
            $valeur_formatee.= ' '.$format ;
        }
        return $valeur_formatee;
    }

    public static function getFormatUm($valeur, $virgule = "1", $format = '')
    {
        $valeur_formatee = number_format($valeur, $virgule, ',', ' ');
        if ($format) {
            $valeur_formatee.= ' '.$format ;
        }
        return $valeur_formatee;
    }

    public static function getFormatMetreCarre($valeur, $virgule = "2", $format = 'm²')
    {
        $valeur_formatee = number_format($valeur, $virgule, ',', ' ') ;
        if ($format) {
            $valeur_formatee.= ' '.$format ;
        }
        return $valeur_formatee;
    }

    public static function getNombreFormat($valeur, $virgule = "2", $separateur = ", ", $separateur_millier = " ")
    {
        if (!$valeur) {
            $valeur=0;
        }
        return number_format($valeur, $virgule, $separateur, $separateur_millier);
    }

    public static function getNombreFormatVirgule($valeur)
    {
        return str_replace(".", ",", $valeur);
    }

    public static function getNombreFormatForfait($valeur)
    {
        return UniteHelper::getNombreFormat($valeur, 1, ',');
    }

    public static function getFormatNombreExcel($valeur, $virgule = "2")
    {
        return UniteHelper::getNombreFormat($valeur, $virgule, ',');
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

    public static function getFormatPerformance($valeur, $virgule = "3", $format = 'kg/hab')
    {
        if (is_numeric($valeur)) {
            $valeur_formatee = number_format($valeur, $virgule, ',', ' ');
            if ($format) {
                $valeur_formatee.= ' '.$format ;
            }
            return $valeur_formatee;
        } else {
            return $valeur;
        }
    }

    public static function toUpperCase($valeur)
    {
        $valeur = strtoupper($valeur);
        return strtr($valeur, "äâàáåãéèëêòóôõöøìíîïùúûüýñçþÿæœðø", "ÄÂÀÁÅÃÉÈËÊÒÓÔÕÖØÌÍÎÏÙÚÛÜÝÑÇÞÝÆŒÐØ");
    }

    public static function floorDecimal($zahl, $decimals = 2)
    {
         return floor($zahl*pow(10, $decimals))/pow(10, $decimals);
    }
}
