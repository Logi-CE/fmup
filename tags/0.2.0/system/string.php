<?php
/**
 * Manipule les chaînes de caractères
 **/

class String
{
    /**
     * Convertir une chaine de la casse chameau à la casse 'underscore'
     * @param String la chaîne à convertir
     **/
    public static function to_Case($chaine)
    {
        return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1_$2', $chaine));
    }
    /**
     * Convertir une chaine de la casse 'underscore' à la casse chameau
     * @param String la chaîne à convertir
     **/
    public static function toCamlCase($chaine)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $chaine)));
    }

    /*
     * encode tout un tableau en UTF8
     */
    public static function utf8EncodeArray($tab = array())
    {
        array_walk_recursive($tab, create_function('&$item, $index', '$item = utf8_encode($item);'));
        return $tab;
    }

    /**
     * Fonction générant une chaine aléatoire
     * @param {taille} : la taille de la chaîne générée
     * la chaîne de caractère aléatoire générée
     */
    public static function genererChaineAleatoire($taille)
    {
        $string = "";
        $user_ramdom_key = "aLABbC0cEd1eDf2FghR3ij4kYXQl5UmOPn6pVq7rJs8tuW9IvGwxHTyKZMS";
        srand((double) microtime()*time());
        for ($i=0; $i<$taille; $i++) {
            $string .= $user_ramdom_key[rand()%strlen($user_ramdom_key)];
        }
        return $string;
    }
	
	/**
     * Fonction déterminant si une chaine est encodée en UTF8 ou non
     * @param {string} : la chaine à examiner
     * @return true si la chaine est en UTF8, false sinon
     */
    public static function isUtf8($string) {
        return preg_match('%(?:
                [\xC2-\xDF][\x80-\xBF]        # non-overlong 2-byte
                |\xE0[\xA0-\xBF][\x80-\xBF]               # excluding overlongs
                |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}      # straight 3-byte
                |\xED[\x80-\x9F][\x80-\xBF]               # excluding surrogates
                |\xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
                |[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
                |\xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
                )+%xs', $string);
    }
}
