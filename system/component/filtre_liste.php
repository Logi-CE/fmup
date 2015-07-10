<?php
/**
 * Classe générant une instance du filtre/liste
 * @author afalaise
 */
class FiltreListe
{
    /**
     * Nom du filtre qui sert à l'enregistrement des filtres
     * @var string
     */
    protected $nom_filtre;
    /**
     * Liste des colonnes sous forme de tableau contenant :
     *         - libelle : Libellé affiché pour le filtre
     *         - nom : Nom du champ recherché
     *         - liaison : Nom du champ réellement affiché dans la colonne et le select (utilisé dans le cas d'un select ou la value et le texte sont différent)
     *         - largeur : Taille prise par le champ, l'unité étant défini par le champ "unite"
     *         - combo : Affiche un select si un tableau est passé en paramètre (sous la forme "valeur => texte")
     *         - lien : Redirection vers le lien d'édition ou utilisation de la fonction passée en paramètre en cas de clic sur la colonne, si le champ n'est pas à faux
     *         - titre : Activer ou non le title au passage de la souris
     *         - obligatoire : Défini que si le champ est vide, la liste ne s'affiche pas
     *         - ifnull : Si la valeur est nulle ou vide (pas zéro), remplace par cet argument
     *         - type : Le type de champ affiché (checkbox, int, date, ....)
     *         - nom_champ_value : dans le cas d'une checkbox, le nom de l'identifiant qui sera la value de la checkbox
     *         - filtre : affichage ou non du filtre
     *         - ordre : affichage ou non du tri
     *         - filtre_defaut : valeur par défaut du filtre
     * @var array
     */
    protected $tableau_champs;
    /**
     * Nom de l'identifiant passé lors du clic sur la ligne
     * @var string
     */
    protected $clef = false;
    /**
     * Filtre supplémentaire pour la requête
     * @var string
     */
    protected $filtre = '';
    /**
     * Tri
     * @var string
     */
    protected $ordre = '';
    /**
     * Défini l'unité de mesure des colonnes
     * @var string
     */
    protected $unite = 'px';
    /**
     * Retour de la page en JSON ou en affichage classique
     * @var bool
     */
    protected $json = false;
    /**
     * Bouton de validation du formulaire, contient les clefs libelle et obligatoire pour supprimer les évènements sur les filtres
     * @var array
     */
    protected $bouton_validation = array();
    /**
     * Options passées au FindAll
     * @var array
     */
    protected $options_supplementaires = array();
    /**
     * Tableau de filtres supplémentaires placés au dessus de la liste. Ce tableau accepte :
     *         - libelle : Libellé affiché pour le filtre
     *         - nom : Nom du champ recherché
     *         - largeur : Taille prise par le champ, en pixel seulement. Par défaut 120
     *         - affichage_libelle : vertical ou horizontal, suivant l'affichage du champ par rapport au libellé
     *         - combo : Affiche un select si un tableau est passé en paramètre (sous la forme "valeur => texte")
     *         - obligatoire : Défini que si le champ est vide, la liste ne s'affiche pas
     *         - id : ID du champ ajouté
     * @var array
     */
    protected $filtre_complementaire = array();
    /**
     * Si défini à faux, la liste ne se chargera pas au démarrage
     * @var bool
     */
    protected $chargement_demarrage = true;
    /**
     * Tableau contenant des entêtes globales à mettre au dessus des colonnes. Ce tableau nécéssite un/plusieurs tableau avec :
     *         - colonne_debut : Numéro de la première colonne de l'entête
     *         - colonne_fin : Numéro de la dernière colonne de l'entête
     *         - libelle : Libellé de l'entête
     * @var array
     */
    protected $colonnes_fusion = array();
    /**
     * Fonction appelée par la liste, par défaut findAll
     * @var string
     */
    protected $fonction_listage = 'findAll';
    /**
     * Colonnes spécifiques pour l'export
     * @var array
     */
    protected $tableau_champs_xls = array();
    /**
     * Le nombre de lignes à afficher pour le tableau.
     * @var int
     */
    protected $nb_lignes_affichees = 25;

    /**
     * Affichage de la pagination
     * @var boolean
     */
    protected $afficher_page = true;

    protected $ligne_total = false;
    
    /**
     * Liste des choix de pagination
     * @var array
     */
    protected $nombre_par_ligne = array(10 => 10, 25 => 25, 50 => 50, 100 => 100, 200 => 200, 500 => 500, 1000 => 1000);
    
    protected $operateurs_filtrage = array('=' => '=', '<' => '<', '>' => '>', '<>' => '<>', '>=' => '>=', '<=' => '<=', '%X' => '%X', 'X%' => 'X%', '%X%' => '%X%');
    
    
    protected $evenement;
    protected $icones;
    protected $x_joins;
    
    protected $unique_id;
    protected $classe;
    protected $classe_filtre;
    protected $chemin = '/system/component/filtre_liste/view/';
    public $timestamp_expiration;
    
    public function __construct($params)
    {
        $this->initialiserAttributs($params);
        
        $this->chemin = BASE_PATH.$this->chemin;
        
        $this->classe_filtre = '';
        
        $this->evenement = "lancerFiltre('".$this->unique_id."')";
        
        $position_top = 0;
        if (!empty($this->filtre_complementaire)) {
            $this->classe_filtre .= ' avec_encart';
            $taille_utilisee = 20;
            foreach ($this->filtre_complementaire as $cpt => $champ) {
                $this->filtre_complementaire[$cpt]['taille_utilisee'] = $taille_utilisee;
                // Paramètres par défaut
                if (!isset($champ['largeur'])) $this->filtre_complementaire[$cpt]['largeur'] = 120;
                if (!isset($champ['affichage_libelle'])) $this->filtre_complementaire[$cpt]['affichage_libelle'] = 'vertical';
                $taille_utilisee += $this->filtre_complementaire[$cpt]['largeur'];
                
            
                if (is_array($champ['nom'])) {
                    $this->filtre_complementaire[$cpt]['liste_noms'] = $champ['nom'];
                    $this->filtre_complementaire[$cpt]['nom'] = $champ['nom'][0];
                } else {
                    $this->filtre_complementaire[$cpt]['liste_noms'] = array($champ['nom']);
                }
                if (!isset($champ['libelle'])) {
                    $this->filtre_complementaire[$cpt]['libelle'] = ' ';
                }
            }
        }

        $left = 0;

        // Calcul des left et right pour chaque colonne fusionnée (si il y en a)
        // On calcul aussi le nombre de colonnes à fusionner et la largeur totale de la div d'entête de ces colonnes
        $colonnes_fusionnes = array();
        foreach ($this->colonnes_fusion as $index => $colonne_fusion) {
            $this->colonnes_fusion[$index]['largeur_totale'] = 0;
            $this->colonnes_fusion[$index]['decalage_gauche'] = 0;
            foreach ($this->tableau_champs as $cpt => $champ) {
                if ($cpt < $colonne_fusion['colonne_debut'] - 1) {
                    $largeur = ($this->unite != '%') ? ($champ['largeur']-1) : $champ['largeur'];
                    $this->colonnes_fusion[$index]['decalage_gauche'] += $largeur;
                } elseif ($cpt <= $colonne_fusion['colonne_fin'] - 1) {
                    $this->colonnes_fusion[$index]['largeur_totale'] += $champ['largeur'];
                    $colonnes_fusionnes[$cpt] = true;
                }
                    
            }
        }

        foreach ($this->tableau_champs as $cpt => $champ) {

            $this->tableau_champs[$cpt]['numero_colonne'] = $cpt;
            
            if (isset($champ['largeur'])) {
                $largeur = $champ['largeur'];
            } else {
                $largeur = '200';
            }
            
            if (!isset($champ['type'])) {
                $this->tableau_champs[$cpt]['type'] = 'texte';
                $champ['type'] = 'texte';
            }
            
            if (is_array($champ['nom'])) {
                $this->tableau_champs[$cpt]['liste_noms'] = $champ['nom'];
                $this->tableau_champs[$cpt]['nom'] = $champ['nom'][0];
            } else {
                $this->tableau_champs[$cpt]['liste_noms'] = array($champ['nom']);
            }
            
            if (!isset($champ['libelle'])) {
                $this->tableau_champs[$cpt]['libelle'] = ' ';
            }
            
            if (isset($champ['libelle_title'])) {
                $this->tableau_champs[$cpt]['libelle_title'] = $champ['libelle_title'];
            } else {
                $this->tableau_champs[$cpt]['libelle_title'] = $this->tableau_champs[$cpt]['libelle'];
            }

            if ($champ['type'] == 'checkbox') {
                $this->tableau_champs[$cpt]['checkbox'] = true;
            } else {
                $this->tableau_champs[$cpt]['checkbox'] = false;
            }

            $this->tableau_champs[$cpt]['top_plus'] = "0";
            $this->tableau_champs[$cpt]['top_moins'] = "";
            if (isset($colonnes_fusionnes[$cpt])) {
                $this->tableau_champs[$cpt]['top_plus'] = "23px";
                $this->tableau_champs[$cpt]['top_moins'] = "top:21px";
            }
            
            // Le champ libre sera exclu de filtre et d'ordre
            if ($champ['type'] == 'champ_libre' || $champ['type'] == 'compteur') {
                $this->tableau_champs[$cpt]['ordre'] = false;
                $this->tableau_champs[$cpt]['filtre'] = false;
            } else {
                if (!isset($champ['ordre'])) {
                    $this->tableau_champs[$cpt]['ordre'] = true;
                }
                if (!isset($champ['filtre'])) {
                    $this->tableau_champs[$cpt]['filtre'] = true;
                }
            }
            
            $largeur = (int) str_replace($this->unite, '', $largeur);
            if ($this->unite != "%") {
                $largeur--;
            }
            
            $this->tableau_champs[$cpt]['largeur'] = $largeur;
            $this->tableau_champs[$cpt]['left'] = $left;

            $left += $largeur;
        }

        if ($this->unite == 'px') {
            $this->classe_filtre .= ' gestion_px';
        }
        
        if (empty($this->tableau_champs_xls)) {
            $this->tableau_champs_xls = $this->tableau_champs;
        }
    }

    public function filtrer ()
    {
        // Remplissage de la session avec le tableau passé en paramètre, le nom de la classe et le libellé de la clef primaire
        $this->timestamp_expiration = time() + Config::getTimeoutSessionId();
        $_SESSION['filtre_liste'][$this->unique_id] = $this;
        
        if (isset($_SESSION['filtres_enregistres'][$this->nom_filtre]['numero_page'])) {
            $numero_page = $_SESSION['filtres_enregistres'][$this->nom_filtre]['numero_page'];
        } else {
            $numero_page = 1;
        }

        $script = "filtre('".$this->unique_id."');";

        ob_start();
        require $this->chemin.'filtrer.php';
        $html = ob_get_clean();
        
        if ($this->chargement_demarrage) {
            if ($this->json) {
                $tableau_json = array('html' => $html, 'script' => $script);
                $retour = json_encode($tableau_json);
            } else {
                $retour = $html.'<script type="text/javascript">'.$script.'</script>';
            }
        } else {
            $retour = $html;
        }
        return $retour;
    }
    
    protected function initialiserAttributs ($params)
    {
        $this->unique_id = uniqid();
        $this->tableau_champs = $params['colonnes'];
        if (isset($params['colonnes_export'])) {
            $this->tableau_champs_xls = $params['colonnes_export'];
        }
        foreach ($params as $libelle => $valeur) { 
            $this->$libelle = $valeur;
        }
    }

    public function afficherFiltre($champ, $nom_filtre)
    {
        $activer_filtrage = true;
        if (isset($champ['activer_filtrage'])) {
            $activer_filtrage = $champ['activer_filtrage'];
        }

        $combo = false;
        if (isset($champ['combo_in'])) {
            $combo = $champ['combo_in'];
        } elseif (isset($champ['combo'])) {
            $combo = $champ['combo'];
        }
        
        $identifiant = "";
        if (isset($champ['id'])) {
            $identifiant = 'id="'.$champ['id'].'"';
        }
        
        if (isset($champ['liste_noms'])) {
            $liste_champs = $champ['liste_noms'];
        } else {
            $liste_champs = array($champ['nom']);
        }

        $valeur_par_defaut = array();
        $valeur_par_defaut['valeur'] = '';
        $valeur_par_defaut['nom'] = $champ['nom'];
        if ($combo) {
            $valeur_par_defaut['operateur'] = '=';
        } else {
            $valeur_par_defaut['operateur'] = '%X%';
        }
        if (isset($champ['filtre_defaut'])) {
            $valeur_par_defaut['valeur'] = $champ['filtre_defaut'];
        } elseif (isset($_SESSION['filtres_enregistres'][$this->nom_filtre][$nom_filtre])) {
            $valeur_par_defaut = $_SESSION['filtres_enregistres'][$this->nom_filtre][$nom_filtre];
        }
        
        $nom_filtre = 'filtre['.$nom_filtre.']';
        
        $filtrage_avance = array();
        if (!empty($champ['filtre_avance'])) {
            if (is_array($champ['filtre_avance'])) {
                $filtrage_avance = $champ['filtre_avance'];
            } else {
                $filtrage_avance = $this->operateurs_filtrage;
            }
        }
        
        $evenement = '';
        if (empty($this->bouton_validation['obligatoire'])) {
            $evenement = $this->evenement;
        }
        
        echo require $this->chemin.'div_filtre.php';
    }
    
    public function lister ($export = false)
    {
        $retour = '';
        
        // Gestion de la purge de la session
        // Les formulaires étant enregistrés en session ils sont constamment présent et alourdissent la mémoire
        // Il faut donc gérer une date de modification de session et une expiration
        if (isset($_SESSION['filtre_liste'])) {
            foreach ($_SESSION['filtre_liste'] as $cle => $session) {
                // Ce serait bête de perdre le filtre courant
                if ($cle != $this->unique_id) {
                    if ($session->timestamp_expiration < time()) {
                        unset($_SESSION['filtre_liste'][$cle]);
                    }
                // Mise à jour de la date d'expiration
                } else {
                    $_SESSION['filtre_liste'][$cle]->timestamp_expiration = time() + Config::getTimeoutSessionId();
                }
            }
        }

        // Nombre de lignes par page
        $top = 0;
        if (!empty($_REQUEST['top'])) {
            $top = $_REQUEST['top'];
        }
        
        // Gestion de la pagination
        $numero_page = $_REQUEST['numero_page'];
        // On sauvegarde la page courante pour ne pas la perdre
        $_SESSION['filtres_enregistres'][$this->nom_filtre]['numero_page'] = $numero_page;
        
        $nb_objets = 0;
        $tableau_objets = array();
        if (isset($_REQUEST['filtre'])) {
            $filtre = $this->traiterFiltre();
            if ($filtre !== false) {
                $options = $this->traiterOption($export, $top, $numero_page);
                $tableau_objets = $this->executerRequete($filtre, $options);
                $nb_objets = $this->compterNbTotal($tableau_objets, $filtre);
            }
        }
        $tableau_donnees = array();
        $tableau_colonnes = array();
        if ($nb_objets > 0) {
            $cpt_ligne = $top * ($numero_page - 1);
            foreach ($tableau_objets as $numero_ligne => $instance) {
                $this->afficherLigne($instance, $numero_ligne, $tableau_donnees, $tableau_colonnes, ++$cpt_ligne, $export);
            }
            
        } else {
            // TODO
            //div aussi large que la somme des colonnes pour forcer l'affichage de la scrollbar
            $largeur_total = 200 * count($this->tableau_champs);
        }

        if ($nb_objets == 0) {
            $numero_page = 1;
            $nb_page = 1;
        } else {
            $nb_page = ceil($nb_objets / $top);
        }
        
        if ($export) {
            $page = 'exporter.php';
        } else {
            $page = 'lister.php';
        }
        
        ob_start();
        require $this->chemin.$page;
        return ob_get_clean();
    }
    
    /**
     * Gère les filtres postés ou par défaut pour les transformer en clauses de requête
     * Il détermine par ailleurs si certains filtres obligatoires sont renseignés
     * @return bool|array : Soit un tableau de clauses utilisables directement par le findAll, soit FAUX pour indiquer qu'il ne faut pas filtrer
     */
    protected function traiterFiltre ()
    {
        $filtrer = true;
        
        $filtre = $this->filtre;
        if (!$filtre) {
            $filtre = array();
        }
        
        // Méthode pour un filtrage particulier
        if (method_exists($this->classe, 'filtrageListes')) {
            $filtre = call_user_func(array($this->classe, 'filtrageListes'), array($_REQUEST['filtre'], $filtre));
        }
    
        $tableau_filtres = $this->tableau_champs;
        if (!empty($this->filtre_complementaire)) {
            foreach ($this->filtre_complementaire as $cpt => $filtre_complementaire) {
                $tableau_filtres['A'.$cpt] = $filtre_complementaire;
            }
        }
        
        foreach ($tableau_filtres as $numero_champ => $champ) {
            if (isset($_REQUEST['filtre'][$numero_champ])) {
                $donnee = $_REQUEST['filtre'][$numero_champ];
                if ($this->nom_filtre) {
                    $_SESSION['filtres_enregistres'][$this->nom_filtre][$numero_champ] = $_REQUEST['filtre'][$numero_champ];
                }
                // On vérifie la présence du champ dans les colonnes paramètrés au départ
                if (in_array($donnee['nom'], $champ['liste_noms'])) {
                    if (isset($champ['numero_colonne'])) {
                        $tableau_champs[$numero_champ]['nom'] = $donnee['nom'];
                    }
                    if ($donnee['valeur'] !== "") {
                        $type = '';
                        if (isset($champ["type"])) {
                            $type = $champ['type'];
                        }
                        $filtre[$numero_champ] = $this->genererClause($donnee['nom'], $donnee['operateur'], $donnee['valeur'], $type);
                    } elseif (!empty($champ['obligatoire'])) {
                        $filtrer = false;
                    }
                } else {
                    $filtrer = false;
                    new Error('Appel à un champ non présent dans le champ de colonne.', E_WARNING);
                }
                if (!empty($champ['partiellement_obligatoire'])) {
                    if (!isset($filtrer_partiellement)) {
                        $filtrer_partiellement = false;
                    }
                    $filtrer_partiellement |= $_REQUEST['filtre'][$champ['nom']] != "";
                }
            }
        }

        // Blocage du filtre
        if (!$filtrer || (isset($filtrer_partiellement) && !$filtrer_partiellement)) {
            $filtre = false;
        }
        
        Console::enregistrer($filtre);
        
        return $filtre;
    }
    
    /**
     * Traite les options postées et par défaut pour la gestion de la requête
     * @param bool $export : Indique si c'est un export ou non
     * @param int $top : Le nombre de lignes par page
     * @param int $numero_page : Le numéro de page courante
     * @return array : Un tableau d'options utilisable directement dans le FindAll d'un objet
     */
    protected function traiterOption ($export, $top, $numero_page)
    {
        $options = array();
        
        // Gestion du ORDER
        $ordre = "";
        if (isset($_REQUEST['ordre_'.$this->unique_id])) {
            $ordre = $_REQUEST['ordre_'.$this->unique_id];
        } elseif (!empty($this->ordre)) {
            $ordre = $this->ordre;
        }
        $options["order"] = $ordre;
        
        // Pas de limite de ligne si export xls
        if ($export) {
            $top = $_REQUEST['nb_elements_total'];
            $numero_page = 1;
        }
        
        $limit = (($numero_page - 1) * $top).','.$top;
        
        // Retour des options
        if (Config::parametresConnexionDb('driver') == 'mysql') {
            $options["limit"] = $limit;
        } elseif (Config::parametresConnexionDb('driver') == 'mssql') {
            $options["top"] = $top;
            $options["paging"] = array(
                    "numero_page" => $numero_page,
                    "nb_element" => $top
            );
        }
        foreach ($this->options_supplementaires as $clef_option => $option) {
            $options[$clef_option] = $option;
        }
        return $options;
    }
    
    /**
     * Exécute la requête de filtrage (généralement FindAll) d'un objet suivant les filtres traités juste avant
     * @param array $filtre : Un tableau contenant différentes clauses pour la requête
     * @param array $options : Un tableau contenant différentes options de traitement
     * @return array[object] : Les éléments retournés sous forme d'un tableau d'objets
     */
    protected function executerRequete ($filtre, $options)
    {
        $tableau_objets = array();
        if (!empty($this->x_joins)) {
            $select = array();
            foreach ($tableau_champs as $champs) {
                if (!isset($champs['type']) || $champs['type'] != 'champ_libre') {
                    if (isset($champs['liaison'])) {
                        $select[$champs['liaison']] = $champs['liaison'];
                    } else {
                        $select[$champs['nom']] = $champs['nom'];
                    }
                }
            }
            if (!empty($this->clef)) {
                $select[$this->clef] = $this->clef;
            }
            $tableau_objets = call_user_func(array($this->classe, $this->fonction_listage), $select, $filtre, $options);
        } else {
            $tableau_objets = call_user_func(array($this->classe, $this->fonction_listage), $filtre, $options);
        }
        return $tableau_objets;
    }
    
    /**
     * Crée un tableau contenant les différentes données nécéssaires à une ligne
     * Ce tableau contient les clés valeur, classe_case, title et clic_ligne
     * @param object $instance : L'objet à afficher
     * @param int $numero_ligne : Le numéro de ligne courante
     * @param array $tableau_donnees : [REF] Le tableau actuel des lignes déjà créées
     * @param array $tableau_colonnes : [REF] Le tableau des colonnes
     * @param int $cpt_ligne : Le compteur actuel des lignes (avec prise en compte de la pagination)
     * @param bool $export : Détermine si l'affichage est pour l'export ou non
     */
    protected function afficherLigne ($instance, $numero_ligne, &$tableau_donnees, &$tableau_colonnes, $cpt_ligne, $export)
    {
        $left = 0;
        
        if ($export) {
            $tableau_champs = $this->tableau_champs_xls;
        } else {
            $tableau_champs = $this->tableau_champs;
        }
        
        foreach ($tableau_champs as $numero_colonne => $instance->champHTML) {
        
            if ($instance->champHTML['type'] == 'compteur') {
                $valeur = $cpt_ligne;
            } elseif ($instance->champHTML['type'] == 'champ_libre') {
                $valeur = '!! TODO !!'; // TODO
            } else {
                $valeur = $this->getAttribute($instance, $export);
            }
        
            if ($export) {
                $tableau_donnees[$numero_ligne][$numero_colonne]["valeur"] = $valeur;
            } else {
        
                // Calcul de la largeur des colonnes
                if (!isset($tableau_colonnes[$numero_colonne])) {
                    if (isset($instance->champHTML['largeur'])) {
                        $largeur = $instance->champHTML['largeur'];
                    } else {
                        $largeur = '200px';
                    }
                    
                    $largeur = (int) str_replace($this->unite, "", $largeur);
                    if ($this->unite != "%") {
                        $largeur--;
                    }
                    $tableau_colonnes[$numero_colonne]["largeur"] = $largeur;
                    $tableau_colonnes[$numero_colonne]["left"] = $left;
                    $tableau_colonnes[$numero_colonne]["max_largeur"] = 0;
                    
                    if ($this->unite != "%") {
                        $left += $largeur + 1;
                    } else {
                        $left += $largeur;
                    }
                }
                $tableau_colonnes[$numero_colonne]["max_largeur"] = max($tableau_colonnes[$numero_colonne]["max_largeur"], strlen($valeur));
        
                // Classe
                if ($numero_ligne % 2) {
                    $classe_case = "liste_div_paire";
                } else {
                    $classe_case = "liste_div_impaire";
                }
                
                if (isset($instance->champHTML['type'])) {
                    $classe_case .= ' type_'.$instance->champHTML['type'];
                }
                if (isset($instance->champHTML['classe'])) {
                    $fonction = $instance->champHTML['classe'];
                    $classe_case .= ' '.$instance->$fonction();
                }
                
                // Lien au clic
                $clic_ligne = '';
                if ($instance->champHTML['lien']) {
                    if ($instance->champHTML['lien'] === true) {
                        $clic_ligne = "location.href='/".call_user_func(array(get_class($instance), 'getControllerName'))."/editer?id=".$instance->getAttribute($this->clef)."&nb=".$cpt_ligne."';";
                    } else {
                        $fonction = $instance->champHTML['lien'];
                        $clic_ligne = $instance->$fonction($cpt_ligne);
                    }
                }
                
                // Infobulle
                $title = '';
                if (isset($instance->champHTML['titre']) && $instance->champHTML['titre'] === true) {
                    if (isset($instance->champHTML['title'])) {
                        $title .= 'title="'.call_user_func(array($instance, 'get'.String::toCamlCase($instance->champHTML['title']))).'"';
                    } else {
                        $title .= 'title="'.addslashes(strip_tags($valeur)).'"';
                    }
                }
                
                $tableau_donnees[$numero_colonne][$numero_ligne]["valeur"] = $valeur;
                $tableau_donnees[$numero_colonne][$numero_ligne]["classe_case"] = $classe_case;
                $tableau_donnees[$numero_colonne][$numero_ligne]["title"] = $title;
                $tableau_donnees[$numero_colonne][$numero_ligne]["clic_ligne"] = $clic_ligne;
            }
        }
    }
    
    protected function genererClause ($nom, $operateur, $valeur, $type = false)
    {
        switch ($operateur) {
            case '=':
            case '<':
            case '>':
            case '<=':
            case '>=':
            case '<>':
                $clause = $operateur." '{{VALEUR}}' ";
                break;
            // TODO : A coder
            case 'BET':
                $clause = "BETWEEN '{{VALEUR_DEBUT}}' AND '{{VALEUR_FIN}}' ";
                break;
            case '%X':
                $clause = "LIKE '%{{VALEUR}}' ";
                break;
            case 'X%':
                $clause = "LIKE '{{VALEUR}}%' ";
                break;
            case '%X%':
            default:
                $clause = "LIKE '%{{VALEUR}}%' ";
                break;
        }
        if (preg_match('#^date_#', $nom) !== 0) {
            // TODO : Mieux gérer les dates
			if (Config::parametresConnexionDb('driver') == 'mysql') {
				$valeur = Sql::sanitize(Date::frToUk($valeur));
			} elseif (Config::parametresConnexionDb('driver') == 'mssql') {
			    $nom = "CONVERT(VARCHAR, ".$nom.", 103)";
				$valeur = Sql::sanitize(Date::frToSql($valeur));
			}
        // TODO : A remettre
        /*} elseif (isset($champ["combo_in"])) {
            $valeur = $donnee['nom']." IN ('".str_replace(",", "','", Sql::sanitize($donnee['valeur']))."') ";
        */} else {
            $valeur = Sql::sanitize($valeur);
        }
        return Sql::sanitize($nom)." ".str_replace('{{VALEUR}}', $valeur, $clause);
    }
    
    protected function getAttribute ($instance, $export)
    {
        if (isset($instance->champHTML['liaison'])) {
            $valeur = call_user_func(array($instance, 'get'.String::toCamlCase($instance->champHTML['liaison'])));
        } else {
            $valeur = call_user_func(array($instance, 'get'.String::toCamlCase($instance->champHTML['nom'])));
        }

        if ($export) {
            $valeur = utf8_decode($valeur);
        }

        //formatage et alignement en fonction du type de colonne
        if (isset($instance->champHTML['ifnull']) && !trim($valeur)) {
            $valeur = $instance->champHTML['ifnull'];
        } elseif (isset($instance->champHTML['type'])) {
            switch ($instance->champHTML['type']) {
                case 'int':
                    $valeur = number_format($valeur, 0, ',', ' ');
                    $valeur = strip_tags($valeur);
                    break;
                case 'float2':
                    $valeur = number_format((float) $valeur, 2, ',', ' ');
                    $valeur = strip_tags($valeur);
                    break;
                case 'monetaire':
                    $valeur = UniteHelper::getFormatMonetaire($valeur);
                    $valeur = strip_tags($valeur);
                    break;
                case 'poids':
                    $valeur = UniteHelper::getFormatKilogramme($valeur);
                    $valeur = strip_tags($valeur);
                    break;
                case 'icone':
                    $valeur = '<img src="'.$instance->champHTML['src-icone'].'">';
                    if (isset($instance->champHTML['titre'])) {
                        $instance->champHTML['titre'] = false;
                    }
                    break;
                case 'date':
                    $valeur = strip_tags($valeur);
                    break;
                case 'sans_html':
                    $valeur = strip_tags($valeur);
                    break;
                case 'flag':
                    $valeur    = ($valeur)? Constantes::imageOui() : Constantes::imageNon();
                    if (isset($instance->champHTML['titre'])) {
                        $instance->champHTML['titre'] = false;
                    }
                    break;
                case 'image':
                    // Rien
                    break;
                case 'flag_inverse':
                    $valeur    = ($valeur)? Constantes::imageNon() : Constantes::imageOui();
                    if (isset($instance->champHTML['titre'])) {
                        $instance->champHTML['titre'] = false;
                    }
                    break;
                case 'checkbox':
                    // TODO
                    $disabled = (isset($instance->champHTML['disabled']) && $instance->champHTML['disabled']) ? 'disabled="disabled"' : '';
                    $value_check = (isset($instance->champHTML['nom_champ_value'])) ? call_user_func(array($instance, 'get'.String::toCamlCase($instance->champHTML['nom_champ_value']))) : 1;
                    $valeur = ($valeur) ? '<input class="checkbox_'.$this->unique_id.'_'.$instance->champHTML['nom'].'" type="checkbox" value="'.$value_check.'" checked="checked" '.$disabled.'>': '<input class="checkbox_'.$this->unique_id.'_'.$instance->champHTML['nom'].'" type="checkbox" value="'.$value_check.'" '.$disabled.'>';
                    break;
                default:
                    $valeur = htmlentities($valeur);
                    break;
            }
        }
        return $valeur;
    }
    
    protected function compterNbTotal ($tableau_objets, $filtre)
    {
        $nb_objets = 0;
        if (count($tableau_objets) > 0) {
            // Cas d'une requête MSSQL utilisant la sous-vue, le résultat total est dans l'attribut REQUETE_NOMBRE_LIGNE de chaque ligne
            if (isset($tableau_objets[0]->REQUETE_NOMBRE_LIGNE)) {
                $nb_objets = $tableau_objets[0]->REQUETE_NOMBRE_LIGNE;
            // Cas d'une requête MySQL utilisant le findAll, le résultat total de la dernière requête exécutée est dans l'attribut $nb_elements du Model
            } elseif (!empty(Model::$nb_elements)) {
                $nb_objets = Model::$nb_elements;
            // Sinon on utilise la fonction count de l'objet lui même, en réutilisant les filtres
            } elseif (method_exists($this->classe, 'count')) {
                $nb_objets = call_user_func(array($this->classe, 'count'), $filtre);
            }
        }
        return $nb_objets;
    }
    
    public function getNumeroDemande ($numero)
    {
        // On reprend les filtres enregistrés
        $tableau_filtres = array();
        if (!empty($_SESSION['filtres_enregistres'][$this->nom_filtre])) {
            $tableau_filtres = $_SESSION['filtres_enregistres'][$this->nom_filtre];
            unset($tableau_filtres['numero_page']);
        }
        // On génère ensuite les clauses
        $filtre = array();
        foreach ($tableau_filtres as $numero_champ => $donnee) {
            if ($donnee['valeur'] !== "") {
                $filtre[$numero_champ] = $this->genererClause($donnee['nom'], $donnee['operateur'], $donnee['valeur']);
            }
        }
        
        $options = $this->traiterOption(false, 1, $numero);
        $tableau_objets = $this->executerRequete($filtre, $options);
        $nb_objets = $this->compterNbTotal($tableau_objets, $filtre);
        
        return $tableau_objets[0];
    }
}
