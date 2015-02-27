<?php
class FileHelper
{
    protected $nom_original;
    protected $type_mime;
    protected $chemin_temporaire;
    protected $taille;
    protected $chemin_destination;
    protected $nom_destination;
    protected $types_mimes_autorises = array();
    protected $erreurs = array();

    /**
     * Constructeur
     * @param string $nom_upload : Nom du champ téléchargé
     */
    public function __construct($nom_upload, $types_mimes_autorises = array())
    {
        if (isset($_FILES[$nom_upload]) && $_FILES[$nom_upload]['error'] == 0) {
            $this->nom_original = $_FILES[$nom_upload]['name'];
            $this->type_mime = $_FILES[$nom_upload]['type'];
            $this->chemin_temporaire = $_FILES[$nom_upload]['tmp_name'];
            $this->taille = $_FILES[$nom_upload]['size'];
            $this->types_mimes_autorises = $types_mimes_autorises;
        } else {
            new Error('Upload impossible du champ '.$nom_upload);
        }

    }

    /**
     * Téléchargement du fichier en lieu et place du chemin passé en paramètre
     * @param string $chemin_destination : [OPT] Dossier de destiantion, par défaut la racine des données (cf constantes)
     * @param string $nom_destination : [OPT] S'il est renseigné, le fichier sera renommé à la destination
     * @return bool : VRAI si le téléchargement s'est bien passé
     */
    public function uploader ($chemin_destination = '', $nom_destination = '')
    {
        $retour = false;
        $this->verifierUpload();
        $this->setCheminDestination($chemin_destination, $nom_destination);
        if (empty($this->erreurs)) {
            $retour = move_uploaded_file($this->chemin_temporaire, $this->chemin_destination.DIRECTORY_SEPARATOR.$this->nom_destination);
        }
        return $retour;
    }

    /**
     * Cette fonction vérifie l'intégrité du téléchargement
     * @return array : La liste eventuelle des erreurs, un tableau vide si aucune erreur n'est détectée
     */
    public function verifierUpload ()
    {
        $this->verifierTypeMime();
        $this->verifierTaille();
        $this->verifierFichier();

        return $this->erreurs;
    }

    protected function setCheminDestination ($chemin_destination = '', $nom_destination = '')
    {
        $this->chemin_destination = Config::paramsVariables('data_path').$chemin_destination;
        if (!$nom_destination) {
            $nom_destination = $this->nom_original;
            // TODO : Gérer si on écrase ou on crée un autre fichier (paramètre ?)
        }
        $this->nom_destination = $nom_destination;
        return $this->verifierDestination();
    }

    protected function verifierTypeMime ()
    {
        $retour = true;
        if (!empty($this->types_mimes_autorises)) {
            if (!in_array($this->type_mime, $this->types_mimes_autorises)) {
                $retour = false;
                $this->erreurs[1] = "Le type mime (".$this->type_mime.") n'est pas autorisé pour ce téléchargement.";
            }
        }
        return $retour;
    }

    protected function verifierTaille ()
    {
        $retour = true;
        if ($this->taille > Config::getMaxSize()) {
            $retour = false;
            $this->erreurs[2] = "La taille du fichier est supérieure à la taille autorisé (".Config::getMaxSize().") par le téléchargement.";
        } elseif ($this->taille == 0) {
            $retour = false;
            $this->erreurs[2] = "La taille du fichier est nulle.";
        }
        return $retour;
        return ($this->taille <= Config::getMaxSize());
    }

    protected function verifierFichier ()
    {
        $retour = true;
        if (!is_uploaded_file($this->chemin_temporaire)) {
            $retour = false;
            $this->erreurs[3] = "Le fichier est inaccessible ou n'a pas été téléchargé.";
        }
        return $retour;
    }

    protected function verifierDestination ()
    {
        $retour  = true;
        if (!is_dir($this->chemin_destination)) {
            if (!mkdir($this->chemin_destination, 0777, true)) {
                $retour = false;
                $this->erreurs[4] = "Le dossier de destination (".$this->chemin_destination.") n'existe pas et n'a pas pu être créé.";
            }
        }
        return $retour;
    }

    public function getTypeMime ()
    {
        return $this->type_mime;
    }

    public function getNomOriginal ()
    {
        return $this->nom_original;
    }

    /**
     * Vérifie qu'un fichier est bien du type attendu
     *
     * @param string $type_mime
     * @return boolean
     */
    public static function verifierTypeFichier($type_mime, $target_type_mime)
    {
        $type_mime = explode('/', $type_mime);
        $mime = $type_mime[0];
        if (isset ($type_mime[1])) {
            $extension = $type_mime[1];
        }
        $type_mime_test = false;
        $extension_test = false;
        if ($target_type_mime == "tout_type") {
            $type_mime_test = true;
            return true;
        }
        if (($mime <> $target_type_mime) && (!($type_mime_test))) {
            return false;
        }
        if (($mime == 'image') && ( ($extension != 'jpeg') && ($extension != 'png'))) {
            return false;
        } elseif (($mime == 'application') && ( ($extension != 'x-shockwave-flash') && ($extension != 'swf'))) {
            return false;
        } else {
            return true;
        }
    }
    /*
     * Renvoi un tableau contenant la liste des types MIME de fichiers autorisés par l'appli
     */
    public static function getTypesFichiersAutorises()
    {
        //Type MIME des fichiers autorisés
        //liste complète : http://www.w3schools.com/media/media_mimeref.asp
        $liste[] = 'image/pjpeg';
        $liste[] = 'image/jpeg';
        $liste[] = 'image/x-png';
        $liste[] = 'image/png';
        $liste[] = 'image/x-bmp';
        $liste[] = 'image/bmp';
        $liste[] = 'text/plain';
        $liste[] = 'application/rtf';
        $liste[] = 'application/msword';
        $liste[] = 'application/vnd.ms-excel';
        $liste[] = 'application/pdf';
        $liste[] = 'application/vnd.ms-powerpoint';
        $liste[] = 'application/zip';
        //$liste[] = '';
        return $liste;
    }
    /**
     * Renvoie un tableau de tous les éléments d'une ligne séparée par des ; (ou autre)
     *
     * @param String $line		La ligne de valeurs
     * @param String $separator Le séparateur
     * @return Array Un tableau de tous les éléments de la ligne
     */
    public static function explodeCSV($line, $separator = ';')
    {
        return array_map(create_function('$o', 'return str_replace("\n", "", str_replace("\r", "", $o));'), explode($separator, $line));
    }

    /**
     * Enter description here...
     *
     * @param FilePointer 	$file		Un pointeur vers un fichier
     * @param String 		$separator	Un séparateur
     * @return Array Un tableau de tableaux correspondant aux lignes et aux colonnes du fichier
     */
    public static function readCSV($file, $separator = ';')
    {
        return array_map(array('FileHelper', 'explodeCSV'), file($file));
    }

    /**
     * Enter description here...
     *
     * @param String $folder dossier vers lequel uploader le fichier
     * @param String $name	 nom du type de fichier (de l'input du form)
     * @return String
     */
    public static function uploadFile($folder, $name)
    {
        $error = '';
        if (isset($_FILES[$name]) && isset($_FILES[$name]['tmp_name'])) {
            $file_to_import = $_FILES[$name]['tmp_name'];
            if (file_exists($file_to_import)) {
                    $tmp_file = $_FILES[$name]['tmp_name'];
                    if (!is_uploaded_file($tmp_file)) {
                        throw new Error('Le fichier est introuvable.');
                    } else {
                        $dossier_destination = "../public/$folder/";
                        $name_file = $_FILES[$name]['name'];
                        if (!move_uploaded_file($tmp_file, $dossier_destination . $name_file)) {
                            throw new Error("Impossible de copier le fichier dans $dossier_destination.");
                        }
                    }
            } else {
                throw new Error("Fichier '$file_to_import' non uploadé");
            }
        }
        return $name_file;
    }

    /**
     * Enter description here...
     *
     * @param String $folder dossier a lister
     * @return tableau de noms de fichiers
     */
    public static function listFiles($folder)
    {
        $result = array();
        if (is_dir($folder)) {
            $dir = opendir($folder);
            // on scanne le répertoire
            while ($p=readdir($dir)) {
                // si c'est un fichier
                if ($p != '..' && $p != '.' && $p != 'cache') {
                    array_push(
                        $result,
                        array(
                            "nom" => "$p",
                            "type" => is_dir("$folder/$p")
                        )
                    );
                }
            }
            closedir($dir);
            return $result;
        } else {
            return false;
        }
    }

    public static function createFolder($folder, $name)
    {
        if (! Is::alphaNumerique($name)) {
            return "Le nom doit être alphanumérique";
        } else {
            if (mkdir($folder.'/'.$name)) {
                return "OK";
            } else {
                return "Erreur à la création du dossier";
            }
        }
    }

    public static function countFiles($folder)
    {
        if (is_dir($folder)) {
            $nb = 0;
            $contenu = FileHelper::listFiles($folder);
            foreach ($contenu as $n => $fichier) {
                $nb++;
            }
            return $nb;
        } else {
            return false;
        }
    }

    /**
      * Enlève les caractères exotiques d'une chaîne de caractères pour en faire un nom de fichier
     **/
    public static function sanitize($txt)
    {
        $a = "àáâãäåòóôõöøèéêëçìíîïùúûüÿñ@!?.:/\\ ";
        $b = "aaaaaaooooooeeeeciiiiuuuuyn________";
        return strtolower(strtr($txt, $a, $b));
    }

    /**
     * Créer un fichier CSV dans le Csv path
     *
     * @param string $nom_fichier
     * @param array(array) $tableau
     * @return bool
     */
    public static function createCsv($nom_fichier, $tableau)
    {
        if ($nom_fichier != "") {
            $nom_fichier = FileHelper::returnNomFichier($nom_fichier);
        } else {
            $nom_fichier = "export";
        }
         if ($f = @fopen(Config::getCsvPath().'/'.$nom_fichier.'.csv', 'w')) {
           foreach ($tableau as $ligne) {
                   fputcsv($f, $ligne, ';');
           }
           fclose($f);
           return true;
         } else {
              return false;
         }
    }

    public static function printCsv($tableau)
    {
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
                ++$count_colonnes;
                if ($count_colonnes == $nombre_colonnes_max) {
                    echo $colonne.";\n";
                } else {
                    echo $colonne.";";
                }
            }
        }
    }

    public static function returnNomFichier($nom_fichier)
    {
        //$hour = getdate();
        //$hour = $hour['hours']."h".$hour['minutes']."m".$hour['seconds']."s";
        //$date = Date::dateForFile();
        $date = date('Ymd_His');
        $nom_fichier = strtolower($nom_fichier);
        //$nom_fichier = $nom_fichier."_".$date."_".$hour;
        $nom_fichier = $nom_fichier."_".$date;
        return $nom_fichier;
    }

    public static function getExtension($nom_fichier)
    {
        if (preg_match('/\.([^\.]*)$/', $nom_fichier, $matches)) {
            return $matches[1];
        } else {
            return '';
        }
    }

    public static function createLogFile($nom_fichier, $chemin = '')
    {
        $nom_fichier = FileHelper::returnNomFichier($nom_fichier) . '.log';
        $_SESSION['nom_fichier'] = $nom_fichier;
        return fopen($chemin.$nom_fichier, 'w');
    }

    public static function writeLogHeader($handle)
    {
        fwrite($handle, '**********************************************'."\r\n");
        fwrite($handle, '*****                                    *****'."\r\n");
        fwrite($handle, '*****  Execution le '. date('d/m/y') .' a '. date('H:i:s') .'  *****'."\r\n");
        fwrite($handle, '*****                                    *****'."\r\n");
        fwrite($handle, '**********************************************'."\r\n\r\n");
    }

    public static function fLog($chaine, $categorie = '')
    {
        $nom_fichier = 'logs_';
        if($categorie != '') $nom_fichier .= $categorie.'_';
        $chemin_fichier = Config::paramsVariables('log_path').$nom_fichier.date('Ymd', time()).'.txt';
        $id_fichier = fopen($chemin_fichier, "a");
        fwrite($id_fichier, "[".date("d/m/Y H:i:s", time())."] ".$chaine."\r\n");
        fclose($id_fichier);
    }

    public static function writeLogLine($handle, $text = '', $nb_newline = 0)
    {
        if (get_resource_type($handle) == 'stream') {
            $newline = str_repeat("\r\n", $nb_newline);
            fwrite($handle, $newline.date('H:i:s')."\t".$text."\r\n");
        }
    }

    public static function getUploadError($error_num)
    {
        switch ($error_num) {
            case 1: // UPLOAD_ERR_INI_SIZE
                return 'Le fichier d&eacute;passe la limite autorisée par le serveur';
                break;
            case 2: // UPLOAD_ERR_FORM_SIZE
                return 'Le fichier d&eacute;passe la limite autorisée dans le formulaire HTML';
                break;
            case 3: // UPLOAD_ERR_PARTIAL
                return 'L\'envoi du fichier a &eacute;t&eacute; interrompu pendant le transfert';
                break;
            case 4: // UPLOAD_ERR_NO_FILE
                return 'Le fichier que vous avez envoy&eacute; a une taille nulle';
                break;
            default:
                return 'Erreur inconnue';
                break;
        }

    }

    /**
     * crée un cartouche formaté a attacher à tout fichier Excel générés par l'application
     *
     * @nom Text titre du document à insérer dans le cartouche
     */
    public static function getCartoucheExcel ($nom)
    {
        $texte =  '<tr>';
        $texte .= '	<td style="font-weight: bold">Titre</td>';
        $texte .= '	<td colspan="3" style="text-align: left">'.$nom.'</td>';
        $texte .= '</tr>';
        $texte .= '<tr>';
        $texte .= '	<td style="font-weight: bold">Date</td>';
        $texte .= '	<td colspan="3" style="text-align: left">'.date('d/m/Y h:i:s').'</td>';
        $texte .= '</tr>';
        $texte .= '<tr>';
        $texte .= '	<td style="font-weight: bold">Version</td>';
        $texte .= '	<td colspan="3" style="text-align: left">'.Config::paramsVariables('version_site').'</td>';
        $texte .= '</tr>';
        $texte .= '<tr></tr><tr></tr>';

        return $texte;
    }

    public static function generateDoc($file_src, $file_desc, array $replace_params = array(), $path_of = "../../data/template/documents", $path_if = "../../data/template/documents")
    {
        if (empty($file_src) || empty($file_desc))
            return false;

        // Lecture du fichier source
        $content = file_get_contents("{$path_if}/{$file_src}");

        // On remplace les mots-clés, un à un
        if (!empty($replace_params)) {
            foreach ($replace_params as $key => $value) {
                $content = str_replace($key, $value, $content);
            }
        }

        //On crée le nouveau fichier généré
        $file = "{$path_of}/{$file_desc}";
        $hdl = fopen($file, "w");
        fwrite($hdl, $content);
        fclose($hdl);
        return $file;
    }
}
