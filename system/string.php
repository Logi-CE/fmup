<?php

/**
 * Classe de manipulation des chaînes de caractères
 * @version 1.0
 */
class String
{
    /**
     * Convertir une chaine de la casse chameau à la casse 'underscore'
     * @param String la chaîne à convertir
     **/
    public static function toSnakeCase($chaine)
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

    /**
     * Passe en majuscule un texte en gérant les principaux caractères spéciaux
     * @param string $valeur : La chaine à convertir
     * @return string : La chaine convertie
     */
    public static function toUpperCase($valeur)
    {
        $valeur = strtoupper($valeur);
        return strtr(
            $valeur,
            "äâàáåãéèëêòóôõöøìíîïùúûüýñçþÿæœðø",
            "ÄÂÀÁÅÃÉÈËÊÒÓÔÕÖØÌÍÎÏÙÚÛÜÝÑÇÞÝÆŒÐØ"
        );
    }

    private static function utf8EncodeFilter(&$item, $index)
    {
        $item = utf8_encode($item);
    }

    /**
     * encode tout un tableau en UTF8
     * @uses self::utf8EncodeFilter
     */
    public static function utf8EncodeArray($tab = array())
    {
        array_walk_recursive($tab, array('\String', 'utf8EncodeFilter'));
        return $tab;
    }

    /**
     * Encodage des caractères spéciaux au format HTML
     * @param String La chaîne à convertir
     */
    public static function htmlEncode($chaine)
    {
        $SAFE_OUT_CHARS = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890._-";
        $result = "";
        for ($i = 0; $i < strlen($chaine); $i++) {
            if (strchr($SAFE_OUT_CHARS, $chaine{$i})) {
                $result .= $chaine{$i};
            } else {
                if (($var = bin2hex(substr($chaine, $i, 1))) <= "7F") {
                    $result .= "&#x" . $var . ";";
                } else {
                    $result .= $chaine{$i};
                }
            }
        }
        return $result;
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
        srand((double)microtime() * time());
        for ($i = 0; $i < $taille; $i++) {
            $string .= $user_ramdom_key[rand() % strlen($user_ramdom_key)];
        }
        return $string;
    }

    /**
     * Coupe une chaine en sous-chaines en essayant autant que possible de couper sur les espaces
     * @param string $chaine : La chaîne à couper
     * @param int $nombre_caracteres : Le nombre de caractères maximum d'une sous-chaine
     * @param string $nombre_coupures_max [OPT] Le nombre de sous-chaines maximum à créer,
     *                                      la dernière comportant le reliquat
     * @return array[string] : Un tableau contenant les sous-chaines
     */
    public static function couper($chaine, $nombre_caracteres = 40, $nombre_coupures_max = false)
    {
        $retour = array($chaine);
        // Pas besoin de couper si la chaine est plus petite
        if (strlen($chaine) > $nombre_caracteres) {
            if (!$nombre_coupures_max) {
                $nombre_coupures_max = 9999;
            }
            $retour = array(0 => '');
            // On coupe la chaine par mots
            $chaine = explode(' ', $chaine);
            $compteur_mots = 0;
            $compteur_lignes = 0;
            while (isset($chaine[$compteur_mots])) {
                if ($nombre_coupures_max > $compteur_lignes + 1) {
                    // On compte la taille avec le mot à ajouter
                    if (strlen($retour[$compteur_lignes] . $chaine[$compteur_mots]) > $nombre_caracteres) {
                        // On vérifie que la sous chaine ait une taille acceptable pour passer à la suivante
                        if (strlen($retour[$compteur_lignes]) > $nombre_caracteres / 2) {
                            $retour[++$compteur_lignes] = $chaine[$compteur_mots];
                        } else {
                            // Le cas échéant on coupe le mot directement
                            $debut_mot = substr($chaine[$compteur_mots], 0, $nombre_caracteres);
                            $retour[$compteur_lignes] .= ' ' . $debut_mot;
                            // On garde le reste mais on indique que le mot n'est pas encore traité
                            $chaine[$compteur_mots] = substr($chaine[$compteur_mots], $nombre_caracteres);
                            $compteur_mots--;
                        }
                    } else {
                        $retour[$compteur_lignes] .= ' ' . $chaine[$compteur_mots];
                    }
                    // Si on a une limite on arrête de couper
                } else {
                    $retour[$compteur_lignes] .= ' ' . $chaine[$compteur_mots];
                }
                $compteur_mots++;
            }
        }

        return $retour;
    }

    /**
     * Fonction déterminant si une chaine est encodée en UTF8 ou non
     * @param {string} : la chaine à examiner
     * @return true si la chaine est en UTF8, false sinon
     */
    public static function isUtf8($string)
    {
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

    /**
     * Takes an UTF-8 string and returns an array of ints representing the
     * Unicode characters. Astral planes are supported ie. the ints in the
     * output can be > 0xFFFF. Occurrances of the BOM are ignored. Surrogates
     * are not allowed.
     *
     * Returns false if the input string isn't a valid UTF-8 octet sequence.
     */
    public static function utf8ToUnicode(&$str)
    {
        $mState = 0;     // cached expected number of octets after the current octet
        // until the beginning of the next UTF8 character sequence
        $mUcs4 = 0;     // cached Unicode character
        $mBytes = 1;     // cached expected number of octets in the current sequence

        $out = array();

        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $in = ord($str{$i});
            if (0 == $mState) {
                // When mState is zero we expect either a US-ASCII character or a
                // multi-octet sequence.
                if (0 == (0x80 & ($in))) {
                    // US-ASCII, pass straight through.
                    $out[] = $in;
                    $mBytes = 1;
                } elseif (0xC0 == (0xE0 & ($in))) {
                    // First octet of 2 octet sequence
                    $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 0x1F) << 6;
                    $mState = 1;
                    $mBytes = 2;
                } elseif (0xE0 == (0xF0 & ($in))) {
                    // First octet of 3 octet sequence
                    $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 0x0F) << 12;
                    $mState = 2;
                    $mBytes = 3;
                } elseif (0xF0 == (0xF8 & ($in))) {
                    // First octet of 4 octet sequence
                    $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 0x07) << 18;
                    $mState = 3;
                    $mBytes = 4;
                } elseif (0xF8 == (0xFC & ($in))) {
                    /* First octet of 5 octet sequence.
                    *
                    * This is illegal because the encoded codepoint must be either
                    * (a) not the shortest form or
                    * (b) outside the Unicode range of 0-0x10FFFF.
                    * Rather than trying to resynchronize, we will carry on until the end
                    * of the sequence and let the later error handling code catch it.
                    */
                    $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 0x03) << 24;
                    $mState = 4;
                    $mBytes = 5;
                } elseif (0xFC == (0xFE & ($in))) {
                    // First octet of 6 octet sequence, see comments for 5 octet sequence.
                    $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 1) << 30;
                    $mState = 5;
                    $mBytes = 6;
                } else {
                    /* Current octet is neither in the US-ASCII range nor a legal first
                    * octet of a multi-octet sequence.
                    */
                    return false;
                }
            } else {
                // When mState is non-zero, we expect a continuation of the multi-octet
                // sequence
                if (0x80 == (0xC0 & ($in))) {
                    // Legal continuation.
                    $shift = ($mState - 1) * 6;
                    $tmp = $in;
                    $tmp = ($tmp & 0x0000003F) << $shift;
                    $mUcs4 |= $tmp;

                    if (0 == --$mState) {
                        /* End of the multi-octet sequence. mUcs4 now contains the final
                         * Unicode codepoint to be output
                         *
                         * Check for illegal sequences and codepoints.
                         */

                        // From Unicode 3.1, non-shortest form is illegal
                        if (((2 == $mBytes) && ($mUcs4 < 0x0080)) ||
                            ((3 == $mBytes) && ($mUcs4 < 0x0800)) ||
                            ((4 == $mBytes) && ($mUcs4 < 0x10000)) ||
                            (4 < $mBytes) ||
                            // From Unicode 3.2, surrogate characters are illegal
                            (($mUcs4 & 0xFFFFF800) == 0xD800) ||
                            // Codepoints outside the Unicode range are illegal
                            ($mUcs4 > 0x10FFFF)
                        ) {
                            return false;
                        }
                        if (0xFEFF != $mUcs4) {
                            // BOM is legal but we don't want to output it
                            $out[] = $mUcs4;
                        }
                        //initialize UTF8 cache
                        $mState = 0;
                        $mUcs4 = 0;
                        $mBytes = 1;
                    }
                } else {
                    /* ((0xC0 & (*in) != 0x80) && (mState != 0))
                     * 
                     * Incomplete multi-octet sequence.
                     */
                    return false;
                }
            }
        }
        return $out;
    }
}
