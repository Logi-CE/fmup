<?php
class DownloadHelper
{
    public static function telechargerDocument($id_document)
    {
        $document 	= Document::findOne($id_document);
        if ($document) {
            $chemin		= Config::getCheminData()."documents/";
            $chemin		.= "document_".$document->getId().TypeFichierHelper::getExtensionDocument($document->getLibelle());

            DownloadHelper::telechargerFichier($chemin, $document->getLibelle(), $document->getTypeMime());

        } else {
            echo 'Document introuvable';
        }
    }

    /**
     * Exporte un tableau de données avec les "instructions" d'un tableau d'entete
     * @param array $tableau_entete : Un tableau contenant "libelle" de l'entete, "formatage" des données (OPT)
     * @param array $tableau_donnees : Un tableau avec les clés correspondantes aux clés d'entete
     * @return array : Un tableau à deux dimensions, les lignes et les colonnes
     */
    public static function exporterTableau ($tableau_entete, $tableau_donnees)
    {
        $fichier = array();
        $compteur_lignes = 0;
        foreach ($tableau_entete as $entete) {
            $fichier[$compteur_lignes][] = $entete['libelle'];
        }
        $compteur_lignes++;
        foreach ($tableau_donnees as $ligne) {
            foreach ($ligne as $numero_colonne => $donnee) {
                if (isset($tableau_entete[$numero_colonne]['formatage']) && method_exists('UniteHelper', $tableau_entete[$numero_colonne]['formatage'])) {
                    $donnee = UniteHelper::$tableau_entete[$numero_colonne]['formatage']($donnee);
                }
                $fichier[$compteur_lignes][$numero_colonne] = $donnee;
            }
            $compteur_lignes++;
        }
        return $fichier;
    }

    public static function telechargerFichier($chemin, $filename, $type_mime = '')
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
            header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
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
            new NotFoundError('Document introuvable : '.$filename);
        }
    }

    public static function getHeaders($filename, $type = 'csv')
    {
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Content-Type: application/force-download');
        if ($type == 'xls') {
			echo('<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />');
            header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        } else {
            header('Content-Type: application/csv-tab-delimited-table; charset="UTF-8"');
        }
    }
}
