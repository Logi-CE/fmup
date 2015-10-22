<?php

class TemplateHelper
{
    // chemin vers le repertoire de template
    private static $pathName = "/data/template";

    public static function getTemplate($file, $type)
    {
        $content = "";
        // verifie que le fichier exists et qu'il est lisible
        if (file_exists(self::$pathName."/{$type}/{$file}.html") && is_readable(self::$pathName."/{$type}/{$file}.html")) {
            // lit et retourne le contenu du fichier template
            if (($content = file_get_contents(self::$pathName."/{$type}/{$file}.html"))) {
                return $content;
            }
        }
        return false;
    }

    public static function getTemplatePdf($file)
    {
        return self::getTemplate($file, "pdf");
    }

    public static function getTemplateMail($file)
    {
        return self::getTemplate($file, "mail");
    }
}
