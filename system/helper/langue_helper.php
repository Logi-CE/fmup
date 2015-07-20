<?php
/**
 * Cette classe permet de gérer le multilangue grâce à un fichier de traduction généré par rapport à une table en BDD
 * @author bbraillon
 */
class LangueHelper
{
    /**
     * Nom de la table de référence des traductions en base de données
     * @var string
     */
    static protected $traduction_name = 'traduction';
    
    /**
     * Nom de la table de référence des langues en base de données
     * @var string
     */
    static protected $langue_name = 'langue';
    
    /**
     * Récupère les traductions directement depuis la base pour la langue donnée des mots demandés
     * @param string $liste_cles : La liste des mots séparé par des virgules
     * @param string $code_langue : [OPT] Le code langue demandé, par défaut il prendra celui courant dans la session "codelanguagedefaut"
     * @return array : Un tableau contenant les traductions sous la forme cle => traduction
     */
    static function getTraductionLive($liste_cles, $code_langue = false)
    {        
        $tabretour = array();
        if (trim($liste_cles) != "") {
            $liste_cles = explode(',', $liste_cles);
            // Sécurisation des mots
            foreach ($liste_cles as $index => $cle) {
                $liste_cles[$index] = Sql::secure(trim($cle));
            }
            if (!$code_langue) {
                $code_langue = $GLOBALS["codelanguagedefaut"];
            }
            
            $sql = "SELECT LT.*
                    FROM ".self::$traduction_name." LT 
                    LEFT JOIN ".self::$langue_name." L
                        ON L.id = LT.id_langue
                    WHERE LT.cle IN (".implode(',', $liste_cles).")
                    AND L.code = '".$code_langue."'";
            $traductions = Model::getDb()->requete($sql);
            foreach ($traductions as $trad) {
                $tabretour[$trad["cle"]] = $trad["traduction"];
            }
        }		
        return $tabretour;
    }

    /**
     * Charge un fichier de traduction pour une langue donnée (et donc la variable globale TRAD)
     * @param string $fichier : Le nom du fichier
     * @param int $code_langue : [OPT] Code langue demandé, par défaut il prendra celui courant dans la session "codelanguagedefaut"
     */
    static function chargerFichier($fichier, $code_langue = false) 
    {
        if (!$code_langue) {
            $code_langue = $GLOBALS["codelanguagedefaut"];
        }
        
        $chemin = Config::paramsVariables('translate_path').trim($code_langue)."/".$fichier;
        
        if (is_file($chemin)) {
            require_once ($chemin);
        } elseif ($code_langue != $GLOBALS["codelanguagedefaut"]) {
            // Puisqu'on a pas trouvé, on charge la langue par défaut
            self::chargerFichier($fichier);
        }
    }
    
    /**
     * Fonction générant un fichier de traduction
     * @param string $nom_fichier : Le nom du fichier à générer (sans le chemin)
     * @param string $code_langue : Le code de langue du fichier à générer
     * @param bool $afficher_retour : [OPT] Une variable indiquant is l'on doit afficher le retour ou non (on l'affiche par défaut)
     */
    static function genererFichierTraductions($nom_fichier, $code_langue, $afficher_retour = true) 
    {
        $code_langue = trim($code_langue);
        
        $chemin = Config::paramsVariables('translate_path');		
        if (!is_dir($chemin)) {
            mkdir($chemin, 0755, true);
            chmod($chemin, 0755);
        }	
        $chemin .= $code_langue."/";
        if (!is_dir($chemin)) {
            mkdir($chemin, 0755, true);
            chmod($chemin, 0755);
        }		
        
        $chemin .= $nom_fichier;
        
        if (is_file($chemin)) {
            unlink($chemin);
        }
        
        $chaine = "<?php\n";
        // Le global est toujours chargé, il ne faut pas redéfinir $GLOBALS['TRAD'] sur les autres fichiers
        if($nom_fichier == "global.ini"){
            $chaine .= "\$GLOBALS['TRAD'] = array(); \n";
        }
        
        $tabTranslated 	= array();
        
        $sql = "SELECT LT.*
                FROM ".self::$traduction_name." LT 
                LEFT JOIN ".self::$langue_name." L
                    ON L.id = LT.id_langue
                WHERE fichier = ".Sql::secure($nom_fichier)."
                    AND L.code = ".Sql::secure($code_langue);
        $traductions = Model::getDb()->requete($sql);
        foreach ($traductions as $trad) {
            $chaine .= "\$GLOBALS['TRAD'][\"".trim($trad["cle"])."\"]";
            $chaine .= " = \"".str_replace("\"", "\\\"", $trad["traduction"])."\";\n";
            $tabTranslated[$trad["traduction"]] = $trad["traduction"];
        }	
        
        $fic = fopen($chemin, "a+");
        fwrite($fic, $chaine);		
        fclose($fic);

        chmod($chemin, 0755);
        if ($afficher_retour) {
            echo "Le fichier ".$code_langue."/".$nom_fichier." a bien été créé <br />";
        }
    }
    
    /**
     * Génère tous les fichiers pour chaque langue présente en base
     */
    static function genererToutesLangues() 
    {        
        $sql = "SELECT DISTINCT fichier, code
                FROM ".self::$langue_name."
                WHERE IFNULL(fichier, '') <> ''";				
        $rsliste = Model::getDb()->requete($sql);
        foreach ($rsliste as $rs) {
            self::genererFichierTraductions($rs["fichier"], $rs["code"]);		
        }
    }

    /**
     * Affiche une traduction à partir de la variable globale TRAD
     * @param string $cle : Le code traduction
     * @return string : La traduction
     */
    public static function display($cle) 
    {
        if (Config::paramsVariables('is_multilingue')) {
            if (isset($GLOBALS['TRAD'][$cle])) {
                return $GLOBALS['TRAD'][$cle];
            } else {
                return "##".$cle."##";
            }
        } else {
            return $cle;
        }
    }
}