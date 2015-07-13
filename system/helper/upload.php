<?php
/**
 * Classe gérant l'upload d'un fichier
 * @author afalaise
 * @version 6.0
 */
class Upload
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
            new Error('Upload impossible du champ '.$nom_upload.' : '.$this->getUploadError());
        }
    }
    
    /**
     * Téléchargement du fichier en lieu et place du chemin passé en paramètre
     * @param string $chemin_destination : [OPT] Dossier de destination, par défaut la racine des données (cf constantes)
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
    
    /**
     * Retourne un message concernant l'erreur serveur de l'upload
     * @return string : Le message
     */
    protected function getUploadError()
    {
        $retour = 'Fichier non trouvé';
        if (isset($_FILES[$nom_upload])) {
            switch ($_FILES[$nom_upload]['error']) {
                case 1: // UPLOAD_ERR_INI_SIZE
                    $retour = 'Le fichier dépasse la limite autorisée par le serveur';
                    break;
                case 2: // UPLOAD_ERR_FORM_SIZE
                    $retour = 'Le fichier dépasse la limite autorisée dans le formulaire HTML';
                    break;
                case 3: // UPLOAD_ERR_PARTIAL
                    $retour = 'L\'envoi du fichier a été interrompu pendant le transfert';
                    break;
                case 4: // UPLOAD_ERR_NO_FILE
                    $retour = 'Le fichier que vous avez envoyé a une taille nulle';
                    break;
            }
        }
        return $retour;
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
        $taille_max = Config::paramsVariables('taille_max_fichier');
        if ($this->taille > $taille_max) {
            $retour = false;
            $this->erreurs[2] = "La taille du fichier est supérieure à la taille autorisé (".$taille_max.") par le téléchargement.";
        } elseif ($this->taille == 0) {
            $retour = false;
            $this->erreurs[2] = "La taille du fichier est nulle.";
        }
        return $retour;
        return ($this->taille <= $taille_max);
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
}