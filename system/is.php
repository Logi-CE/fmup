<?php

/**
 * Cette classe contient des fonctions de vérification de format ou de type
 * @version 1.0
 */
class Is
{
    /**
     * Vérifie si le paramètre est un ID correct
     * @param mixed $valeur : La variable testée
     * @return bool : VRAI si le paramètre est un entier positif (0 toléré)
     */
    public static function id($valeur)
    {
        if (!self::chaineOuNombre($valeur)) {
            $retour = false;
        } else {
            $retour = (bool)preg_match('#^[0-9]+$#', $valeur);
        }
        return $retour;
    }

    /**
     * Vérifie si le paramètre est un entier
     * @param mixed $valeur : La variable testée
     * @return bool : VRAI si le paramètre est un entier
     */
    public static function integer($valeur)
    {
        if (!self::chaineOuNombre($valeur)) {
            $retour = false;
        } else {
            $retour = (bool)preg_match('#^-?[0-9]+$#', $valeur);
        }
        return $retour;
    }

     /**
     * Vérifie si la valeur décimale du paramètre est égale à un demi de un
     * @param mixed $valeur : La variable testée
     * @return bool : VRAI si la valeur décimale du paramètre est égale à un demi de un
     */
    public static function half($valeur)
    {
        return (bool) (self::chaineOuNombre($valeur) && abs($valeur - floor($valeur) - 0.5) < 0.0001);
    }
    
    /**
     * Vérifie si le paramètre est un décimal
     * @param mixed $valeur : La variable testée
     * @return bool : VRAI si le paramètre est un décimal (virgule ou point acceptés)
     */
    public static function decimal($valeur)
    {
        if (!self::chaineOuNombre($valeur)) {
            $retour = false;
        } else {
            $retour = (bool)preg_match('#^-?(?:[0-9]+[\.,]?[0-9]*|[0-9]*[\.,]?[0-9]+)$#', $valeur);
        }
        return $retour;
    }

    /**
     * Cette fonction teste si le paramètre est true ou false
     * @param mixed $valeur : La variable testée
     * @return bool : VRAI si le paramètre est un booléen
     */
    public static function booleen($valeur)
    {
        $retour = false;
        if ($valeur === true || $valeur === false) {
            $retour = true;
        }
        return $retour;
    }

    /**
     * Cette fonction teste si le paramètre un nombre pair
     * @param mixed $valeur : La variable testée
     * @return bool : VRAI si le paramètre est un nombre et qu'il est pair
     */
    public static function pair($valeur)
    {
        $retour = false;
        if (self::integer($valeur) && !($valeur % 2)) {
            $retour = true;
        }
        return $retour;
    }

    /**
     * Cette fonction teste le type du paramètre
     * @param mixed $valeur : La variable testée
     * @return bool : VRAI si le paramètre n'est pas un tableau, un booléen ou un objet
     */
    public static function chaineOuNombre($valeur)
    {
        return !(is_object($valeur) || is_array($valeur) || self::booleen($valeur));
    }

    /**
     * Teste si le paramètre passé est alphanumérique
     * @param string $valeur : La valeur doit être comprise entre "0" et "9", "A" et "z", "a" et "z" ou être "_"
     * @return : VRAI si la valeur passée en paramètre est alphanumérique
     */
    public static function alphaNumerique($valeur)
    {
        if (!self::chaineOuNombre($valeur)) {
            $retour = false;
        } else {
            $retour = (bool)preg_match('#^\w*$#', $valeur);
        }
        return $retour;
    }

    /**
     * Fonction vérifiant le numéro de téléphone passé en paramètre
     * @param mixed $valeur : La variable testée
     * @return bool
     *          VRAI si la chaine passée en paramètre est un numéro de téléphone
     *          valide faisant de 10 à 20 caractères,
     *          espaces, "+" et "." tolérés
     */
    public static function telephone($valeur)
    {
        if (!self::chaineOuNombre($valeur)) {
            $retour = false;
        } else {
            $retour = (bool)preg_match('#^(\+){0,1}(\d|\s|\(|\)|\.){10,20}$#i', $valeur);
        }
        return $retour;
    }

    /**
     * Fonction vérifiant le numéro de téléphone portable
     * @param mixed $valeur : La variable testée
     * @return bool
     *          VRAI si le paramètre est une chaine numérique de 10 caractères commençant par 06 ou 07,
     *          pas de séparateur toléré
     */
    public static function telephonePortable($valeur)
    {
        if (!self::chaineOuNombre($valeur)) {
            $retour = false;
        } else {
            $retour = (bool)preg_match('#^0(6|7)(\d){8}$#i', $valeur);
        }
        return $retour;
    }

    /**
     * Fonction déterminant si une valeur est un numéro de compte général
     * @param mixed $valeur : La variable testée
     * @return bool : VRAI si le paramètre est une chaine numérique de 14 caractères maximum
     */
    public static function compteGeneral($valeur)
    {
        if (!self::chaineOuNombre($valeur)) {
            $retour = false;
        } else {
            $retour = (bool)preg_match('#^[0-9]{1,14}$#', $valeur);
        }
        return $retour;
    }

    /**
     * Fonction déterminant si une valeur est un numéro de compte tiers
     * @param mixed $valeur : La variable testée
     * @return bool : VRAI si le paramètre est une chaine alphanumérique de 17 caractères maximum
     */
    public static function compteTiers($valeur)
    {
        if (!self::chaineOuNombre($valeur)) {
            $retour = false;
        } else {
            $retour = (bool)preg_match('#^\w{1,17}$#', $valeur);
        }
        return $retour;
    }

    /**
     * Valide que la date donnée au format français (SANS heure) existe bien
     * @param mixed $valeur : La variable testée
     * @return bool
     *      VRAI si la valeur passée en paramètre une date au format JJ/MM/AAAA ou JJ/MM/AA
     *      avec comme séparateur / ou . ou -
     */
    public static function date($valeur)
    {
        if (is_string($valeur)) {
            $resultat = preg_split('|[/.-]|', $valeur);
            if (count($resultat) == 3) {
                list($jour, $mois, $annee) = $resultat;
                if (Is::integer($jour) && Is::integer($mois) && Is::integer($annee)) {
                    if (strlen($annee) == 2) {
                        $annee = '20' . $annee;
                    }
                    return ($annee < 1000 || $annee > 9999) ? false : checkDate($mois, $jour, $annee);
                }
            }
        }
        return false;
    }
    
     /**
     * Valide que la date donnée au format français (SANS heure) existe bien
     * @param mixed $valeur : La variable testée
     * @return bool : VRAI si la valeur passée en paramètre une date au format JJMMAAAA ou JJMMAA, sans séparateur
     */
    public static function dateWithoutSeparator($valeur) {
        if (is_string($valeur)) {
            if (strlen($valeur) == 8) {
                $jour = substr($valeur, -8, 2);
                $mois = substr($valeur, -6, 2);
                $annee = substr($valeur, -4);
            } elseif (strlen($valeur) == 6) {
                $jour = substr($valeur, -6, 2);
                $mois = substr($valeur, -4, 2);
                $annee = substr($valeur, -2);
            } else {
                return false;
            }
            if (Is::integer($jour) && Is::integer($mois) && Is::integer($annee)) {
                if (strlen($annee) == 2)
                    $annee = '20' . $annee;
                if ($annee < 1000)
                    return false;
                if ($annee > 9999)
                    return false;
                return checkDate($mois, $jour, $annee);
            }
        }
        return false;
    }

    /**
     * Valide que la date donnée au format américain (SANS heure) existe bien
     * @param mixed $valeur : La variable testée
     * @return bool
     *      VRAI si la valeur passée en paramètre une date zu format AAAA-MM-JJ ou AA-MM-JJ
     *      avec comme séparateur / ou . ou -
     */
    public static function dateUk($valeur)
    {
        if (is_string($valeur)) {
            $resultat = preg_split('|[/.-]|', $valeur);
            if (count($resultat) == 3) {
                list($annee, $mois, $jour) = $resultat;
                if (Is::integer($jour) && Is::integer($mois) && Is::integer($annee)) {
                    if (strlen($annee) == 2) {
                        $annee = '20' . $annee;
                    }
                    return ($annee < 1000 || $annee > 9999) ? false : checkDate($mois, $jour, $annee);
                }
            }
        }
        return false;
    }

    /**
     * Valide que la date donnée au format français (avec ou sans heure) existe bien
     * @param mixed $valeur : La variable testée
     * @return bool
     *          VRAI si la valeur passée en paramètre une date au format JJ/MM/AAAA ou JJ/MM/AA
     *          avec comme séparateur / ou . ou -
     */
    public static function dateTime($valeur)
    {
        $retour = false;
        if (is_string($valeur)) {
            $resultat = preg_split('|\ |', $valeur);
            $retour = self::date($resultat[0]);
            // Cas date + heure
            if (count($resultat) == 2) {
                $retour = $retour && self::heure($resultat[1]);
            }
        }
        return $retour;
    }

    /**
     * Valide que la date donnée au format américain (avec ou sans heure) existe bien
     * @param mixed $valeur : La variable testée
     * @return bool
     *      VRAI si la valeur passée en paramètre une date au format AAAA-MM-JJ ou AA-MM-JJ
     *      avec comme séparateur / ou . ou -
     */
    public static function dateTimeUk($valeur)
    {
        $retour = false;
        if (is_string($valeur)) {
            $resultat = preg_split('|\ |', $valeur);
            $retour = self::dateUk($resultat[0]);
            // Cas date + heure
            if (count($resultat) == 2) {
                $retour = $retour && self::heure($resultat[1]);
            }
        }
        return $retour;
    }

    /**
     * Teste si la chaine passée en paramètre est une adresse email valide
     * @param mixed $valeur : La variable testée
     * @return bool : VRAI si la valeur passée en paramètre est une adresse email valide
     */
    public static function courriel($valeur)
    {
        if (!self::chaineOuNombre($valeur)) {
            $retour = false;
        } else {
            $retour = (bool)preg_match('#^[a-z0-9]+[-\w\?\.\+]*@[-\w]+(?:\.[-\w]+)*\.[a-z]{2,5}$#i', $valeur);
            if ($retour && (bool)preg_match('#\.\.#', $valeur)) {
                $retour = false;
            }
        }
        return $retour;
    }

    /**
     * Teste si la chaine passée en paramètre est une heure valide
     * @param mixed $valeur : La variable testée
     * @return bool
     *      VRAI si la valeur passée en paramètre est une heure valide
     *      sous la forme hh:mm:ss, secondes non obligatoires
     */
    public static function heure($valeur)
    {
        if (!self::chaineOuNombre($valeur)) {
            $retour = false;
        } else {
            $retour = (bool)preg_match('#^(([01][0-9])|(2[0-3]))(:[0-5][0-9])(:[0-5][0-9])?$#', $valeur);
        }
        return $retour;
    }

    /**
     * Teste si la chaine passée en paramètre est un RIB valide
     * @param mixed $valeur : La variable testée
     * @return bool
     *      VRAI si la valeur passée en paramètre est un RIB valide
     *      (chaine texte de 24 caractères, espaces bien placés tolérés)
     */
    public static function rib(&$valeur)
    {
        $regexp = '|^\s*(?P<cbanque>\d{5})\s*' .
            '(?P<cguichet>\d{5})\s*(?P<nocompte>[a-z0-9]{11})\s*(?P<clerib>\d{2})\s*$|i';
        if (!self::chaineOuNombre($valeur) ||
            !preg_match($regexp, $valeur, $matches)
        ) {
            return false;
        }
        extract($matches);
        $valeur = $cbanque . $cguichet . $nocompte . $clerib;
        $tabcompte = "";
        $len = strlen($nocompte);
        if ($len != 11) {
            return false;
        }
        for ($i = 0; $i < $len; $i++) {
            $caractere = substr($nocompte, $i, 1);
            if (!is_numeric($caractere)) {
                $car = ord($caractere) - 64;
                $nombre = ($car < 10) ? $car : (($car < 19) ? $car - 9 : $car - 17);
                $tabcompte .= $nombre;
            } else {
                $tabcompte .= $caractere;
            }
        }
        $int = $cbanque . $cguichet . $tabcompte . $clerib;
        return (strlen($int) >= 21 && bcmod($int, 97) == 0);
    }
}
