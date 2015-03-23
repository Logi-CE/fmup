<?php
class TypeFichierHelper
{
    public static function imageFichier ($fichier)
    {
        $tableau = explode(".", $fichier);
        switch (strtolower($tableau[count($tableau)-1])) {
            case "xls":
            case "xlsx":
            case "csv":
                $retour = Images::iconeExcel();
                break;
            case "rtf":
            case "doc":
            case "docx":
                $retour = Images::iconeWord();
                break;
            case "txt":
                $retour = Images::iconeBlocNote();
                break;
            case "pdf":
                $retour = Images::iconePDF();
                break;
            case "jpg":
            case "jpeg":
            case "gif":
            case "tif":
            case "png":
                $retour = Images::iconeImage();
                break;
            default:
                $retour = Images::iconeInconnu();
        }
        return $retour;
    }
    public static function getExtensionDocument($value)
    {
        //Julien : J'aime pas trop la ...
        /*if (strrpos($value, ".") !== false) {
            return substr($value, strrpos($value, "."));
        }*/
        if (preg_match('/\.([^\.]*)$/', $value, $matches)) {
            return '.' . $matches[1];
        }

        return "";
    }
}
