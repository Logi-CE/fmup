<?php

/**
 * Classe gérant diverses opérations sur les fichiers
 * @version 1.0
 */
class FileHelper
{
    /**
     * Retourne la liste des fichiers d'un dossier
     * @param string $dossier : Nom du dossier à lister
     * @return array : Tableau de noms de fichiers
     */
    public static function listerFichier($dossier)
    {
        $liste_fichiers = array();
        if (is_dir(BASE_PATH . '/' . $dossier)) {
            $dir = opendir(BASE_PATH . '/' . $dossier);
            // on scanne le répertoire
            while ($fichier = readdir($dir)) {
                // si c'est un fichier
                if ($fichier != '..' && $fichier != '.' && $fichier != 'cache') {
                    // On l'ajoute
                    $liste_fichiers[] = $fichier;
                }
            }
            closedir($dir);
        }

        return $liste_fichiers;
    }

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
     * Crée un formatage CSV d'un tableau de données
     * @param string $tableau : Un tableau de données en deux dimensions [ligne][colonne] = valeur
     * @param string $separateur_colonne : [OPT] Le séparateur de colonne, par défaut ";"
     * @param string $separateur_ligne : [OPT] Le séparateur de ligne, par défaut "\n"
     * @return string : Le fichier sous forme de texte
     */
    public static function formaterCsv($tableau, $separateur_colonne = ";", $separateur_ligne = "\n")
    {
        $retour = '';
        $nombre_colonnes_max = 0;
        foreach ($tableau as $ligne) {
            $nombre_colonnes_tmp = count($ligne);
            if ($nombre_colonnes_tmp > $nombre_colonnes_max) {
                $nombre_colonnes_max = $nombre_colonnes_tmp;
            }
        }
        foreach ($tableau as $ligne) {
            $count_colonnes = 0;
            foreach ($ligne as $colonne) {
                $retour .= $colonne . $separateur_colonne;
                if (++$count_colonnes >= $nombre_colonnes_max) {
                    $retour .= $separateur_ligne;
                }
            }
        }
        return $retour;
    }

    /**
     * Crée un fichier de log pour y mettre les données en paramètre
     * @param string $nom_fichier : Le nom (en partie) du fichier
     * @param mixed $message : La chaine à loguer
     * @param string $periodicite : [OPT] Détermine la périodicité des fichiers,
     *              il est placé à la fin du nom du fichier avec la fonction date, par défaut tous les jours
     */
    public static function fLog($nom_fichier, $message, $periodicite = 'Ymd')
    {
        if (is_array($message) || is_object($message)) {
            $message = print_r($message, true);
        }
        $nom_fichier = 'logs_' . strtr($nom_fichier, '/\\', '__') . '_' . date($periodicite) . '.log';
        $chemin_fichier = Config::paramsVariables('log_path') . $nom_fichier;
        $id_fichier = fopen($chemin_fichier, "a");
        fwrite($id_fichier, "[" . date("d/m/Y H:i:s") . "] " . $message . "\r\n");
        fclose($id_fichier);
    }

    /**
     * Crée un cartouche formaté a attacher à tout fichier Excel généré par l'application
     * @param string nom : titre du document à insérer dans le cartouche
     */
    public static function getCartoucheExcel($nom)
    {
        $texte = '<tr>';
        $texte .= '	<td style="font-weight: bold">Titre</td>';
        $texte .= '	<td colspan="3" style="text-align: left">' . $nom . '</td>';
        $texte .= '</tr>';
        $texte .= '<tr>';
        $texte .= '	<td style="font-weight: bold">Date</td>';
        $texte .= '	<td colspan="3" style="text-align: left">' . date('d/m/Y h:i:s') . '</td>';
        $texte .= '</tr>';
        $texte .= '<tr>';
        $texte .= '	<td style="font-weight: bold">Version</td>';
        $texte .= '	<td colspan="3" style="text-align: left">' . Config::paramsVariables('version_site') . '</td>';
        $texte .= '</tr>';
        $texte .= '<tr></tr><tr></tr>';

        return $texte;
    }

    /**
     * Crée un document à partir d'un template en y remplaçant des expressions
     * @param unknown $fichier_modele : Le nom du template
     * @param unknown $nom_fichier : Le nom complet du fichier avec le chemin
     * @param array $tableau_remplacements : [OPT] Un tableau contenant tous les remplacements à faire,
     *                                  sous la forme origine => remplacement
     * @return bool : VRAI si totu s'est bien passé
     */
    public static function genererDocumentParModele($fichier_modele, $nom_fichier, $tableau_remplacements = array())
    {
        $retour = false;

        // Lecture du fichier source
        $chemin_modele = Config::paramsVariables('template_path') . $fichier_modele;
        $contenu = file_get_contents($chemin_modele);

        // On remplace les mots-clés, un à un
        if (!empty($tableau_remplacements)) {
            foreach ($tableau_remplacements as $cle => $valeur) {
                $contenu = str_replace($cle, $valeur, $contenu);
            }
        }

        // On crée le nouveau fichier généré
        $nouveau_fichier = fopen($nom_fichier, "w");
        if ($nouveau_fichier) {
            fwrite($nouveau_fichier, $contenu);
            fclose($nouveau_fichier);
            $retour = true;
        }
        return $retour;
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

    /**
     * @param $handle
     * @param string $text
     * @param int $nb_newline
     * @deprecated you might use fLog. May I suggest Monolog system ?
     * @see \FMUP\Bootstap::getLogger
     */
    public static function writeLogLine($handle, $text = '', $nb_newline = 0)
    {
        if (get_resource_type($handle) == 'stream') {
            $newline = str_repeat("\r\n", $nb_newline);
            fwrite($handle, $newline . date('H:i:s') . "\t" . $text . "\r\n");
        }
    }
}
