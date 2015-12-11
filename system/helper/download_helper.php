<?php

/**
 * Classe gérant le téléchargement de fichiers
 */
class DownloadHelper
{
    /**
     * @param {ouverture_navigateur} : Spécifie si le fichier sera ouvert par firefox (true),
     * ou qu'une boite de dialogue s'ouvrira proposant de télécharger le fichier (false par défaut)
     */
    public static function telechargerFichier($chemin, $filename, $type_mime = '', $ouverture_navigateur = false)
    {
        if (is_file($chemin)) {
            /*
             //detection type mime php >= 5.3
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $type_mime = finfo_file($finfo, $chemin);
            finfo_close($finfo);
            */
            if (!empty($type_mime)) {
                if ((function_exists('mime_content_type'))) {
                    $type_mime = mime_content_type($chemin);
                } else {
                    $type_mime = 'octet/stream';
                }
            }

            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private", false);
            if ($ouverture_navigateur) {
                header("Content-Disposition: filename=\"" . $filename . "\"");
            } else {
                header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
            }
            header("Content-Type: \"$type_mime\"");
            //if (!empty($type_mime)) header("Content-Type: " . $type_mime);
            header("Content-Transfer-Encoding:­ binary");
            header("Content-Description: File Transfer");
            //header("Content-Length: " . filesize($chemin));
            ini_set('max_execution_time', 0);
            flush(); // this doesn't really matter.

            $pfic = fopen($chemin, "r");
            while (!feof($pfic)) {
                echo fread($pfic, 4096);
                flush(); // this is essential for large downloads
            }
            fclose($pfic);
        } else {
            throw new \FMUP\Exception\Status\NotFound('Document introuvable : ' . $filename);
        }
    }

    public static function getHeaders($filename, $type = 'csv')
    {
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Type: application/force-download');
        if ($type == 'xls') {
            echo('<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />');
            header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        } else {
            header('Content-Type: application/csv-tab-delimited-table; charset="UTF-8"');
        }
    }
}
