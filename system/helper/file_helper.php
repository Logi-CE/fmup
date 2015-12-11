<?php

/**
 * Classe gérant diverses opérations sur les fichiers
 * @version 1.0
 */
class FileHelper
{
    /**
     * Enlève les caractères exotiques d'une chaîne de caractères pour en faire un nom de fichier
     */
    public static function sanitize($txt)
    {
        $a = "àáâãäåòóôõöøèéêëçìíîïùúûüÿñ@!?.:/\\ ";
        $b = "aaaaaaooooooeeeeciiiiuuuuyn________";
        return strtolower(strtr($txt, $a, $b));
    }

    /**
     * @param string $nom_fichier
     * @return string
     */
    public static function getExtension($nom_fichier)
    {
        if (preg_match('/\.([^\.]*)$/', $nom_fichier, $matches)) {
            return $matches[1];
        } else {
            return '';
        }
    }
}
