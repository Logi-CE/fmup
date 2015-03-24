<?php

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
     *         - lien : Redirection vers un lien particulier en cas de clic sur la colonne, si le champ n'est pas à vrai
     *         - titre : Activer ou non le title au passage de la souris
     *         - obligatoire : Défini que si le champ est vide, la liste ne s'affiche pas
     *         - ifnull : Si la valeur est nulle ou vide (pas zéro), remplace par cet argument
     *         - type : Le type de champ affiché (checkbox, int, date, ....)
     *         - nom_champ_value : dans le cas d'une checkbox, le nom de l'identifiant qui sera la value de la checkbox
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
     * Si on veut que le clic sur la ligne redirige vers un autre controlleur que celui défini par le modèle
     * @var string
     */
    protected $destinationCtrl = "";
    /**
     * Si on veut que le clic sur la ligne redirige vers un autre controlleur, on indique la clef à utiliser
     * @var string
     */
    protected $clef_destination_ctrl = "";
    /**
     * Options passées au FindAll
     * @var array
     */
    protected $options_supplementaires = array();
    /**
     * On peut plutôt que d'utiliser le FindAll du modèle utiliser une requête spécifique (appel à la fonction executerRequeteSpecifique avec le paramètre en argument)
     * @var bool
     */
    protected $requete_specifique = '';
    /**
     * Données supplémentaires ajoutées sur le clic de la ligne
     * @var string
     */
    protected $datas_complementaires_postees = '';
    /**
     * Le clic sur la ligne ouvrira une nouvelle page
     * @var bool
     */
    protected $ouverture_nouvelle_page_si_click = '';
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
    protected $fonction_listage = '';
    /**
     * Colonnes spécifiques pour l'export
     * @var array
     */
    protected $tableau_champs_xls = array();
    /**
     * Le nombre de lignes à afficher pour le tableau. 0 pour afficher tous les résultats
     * @var int
     */
    protected $nb_lignes_affichees = 25;
    /**
     * Liste des choix de pagination
     * @var array
     */
    protected $nombre_par_ligne = array(25 => 25, 50 => 50, 100 => 100, 200 => 200, 500 => 500, 1000 => 1000, 0 => 'Sans limite');
    
    
    protected $icones;
    
    protected $unique_id;
    protected $classe;
    protected $template = 'filtre_liste/filtre.php';
    protected $style_liste = array();
    protected $style_filtre = array();
    protected $liste_colonne = array();
    
    public function __construct($params)
    {
        $this->initialiserAttributs($params);
        
        $position_top = 0;
        if (!empty($this->filtre_complementaire)) {
            $taille_utilisee = 0;
            foreach ($this->filtre_complementaire as $i => $champ) {
                $this->filtre_complementaire[$i]['taille_utilisee'] = $taille_utilisee;
                // Paramètres par défaut
                if (!isset($champ['largeur'])) $this->filtre_complementaire[$i]['largeur'] = 120;
                if (!isset($champ['affichage_libelle'])) $this->filtre_complementaire[$i]['affichage_libelle'] = 'vertical';
                $taille_utilisee += $this->filtre_complementaire[$i]['largeur'];
            }
            $this->style_liste[] = 'margin-top: 40px';
            $this->style_filtre[] = 'top: 40px';
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

            if (isset($champ['largeur'])) {
                $largeur = $champ['largeur'];
            } else {
                $largeur = '200';
            }

            if (!isset($champ['libelle'])) {
                $this->tableau_champs[$cpt]['libelle'] = ' ';
            }
            
            if (isset($champ['libelle_title'])) {
                $this->tableau_champs[$cpt]['libelle_title'] = $champ['libelle_title'];
            } else {
                $this->tableau_champs[$cpt]['libelle_title'] = $this->tableau_champs[$cpt]['libelle'];
            }

            if (!isset($champ['filtre'])) {
                $this->tableau_champs[$cpt]['filtre'] = true;
            }
            
            if (isset($champ['type']) && $champ['type'] == 'checkbox') {
                $this->tableau_champs[$cpt]['checkbox'] = true;
            } else {
                $this->tableau_champs[$cpt]['checkbox'] = false;
            }

           $this->liste_colonne[] = $champ['nom'];

            $this->tableau_champs[$cpt]['top_plus'] = "0";
            $this->tableau_champs[$cpt]['top_moins'] = "";
            if (isset($colonnes_fusionnes[$cpt])) {
                $this->tableau_champs[$cpt]['top_plus'] = "23px";
                $this->tableau_champs[$cpt]['top_moins'] = "top:21px";
            }
            
            // pas d'ordre by possible
            if (isset($champ['type']) && ($champ['type']=='icone' || $champ['type']=='bouton' || $champ['type']=='champ_libre')) {
                $this->tableau_champs[$cpt]['affichage_ordre'] = false;
            } else {
                $this->tableau_champs[$cpt]['affichage_ordre'] = true;
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
            $this->style_liste[] = 'overflow-x: auto';
        } else {
            $this->style_liste[] = 'overflow-x: hidden';
        }
    }

    public function afficher ()
    {
        // Remplissage de la session avec le tableau passé en paramètre, le nom de la classe et le libellé de la clef primaire
        $_SESSION[$this->unique_id]['tableau_champs'] = $this->tableau_champs;
        $_SESSION[$this->unique_id]['classe'] = $this->classe;
        $_SESSION[$this->unique_id]['clef'] = $this->clef;
        $_SESSION[$this->unique_id]['filtre'] = $this->filtre;
        $_SESSION[$this->unique_id]['unite'] = $this->unite;
        $_SESSION[$this->unique_id]['tableau_champs_xls'] = $this->tableau_champs_xls;
        $_SESSION[$this->unique_id]['destinationCtrl'] = $this->destinationCtrl;
        $_SESSION[$this->unique_id]['clef_destination_ctrl'] = $this->clef_destination_ctrl;
        $_SESSION[$this->unique_id]['options_supplementaires'] = $this->options_supplementaires;
        $_SESSION[$this->unique_id]['filtre_complementaire'] = $this->filtre_complementaire;
        $_SESSION[$this->unique_id]['requete_specifique'] = $this->requete_specifique;
        $_SESSION[$this->unique_id]['datas_complementaires_postees'] = $this->datas_complementaires_postees;
        $_SESSION[$this->unique_id]['ouverture_nouvelle_page_si_click'] = $this->ouverture_nouvelle_page_si_click;
        $_SESSION[$this->unique_id]['nom_filtre'] = $this->nom_filtre;
        $_SESSION[$this->unique_id]['fonction_listage'] = $this->fonction_listage;
        $_SESSION[$this->unique_id]['icones'] = $this->icones;

        $script = "filtre('".$this->unique_id."');";

        ob_start();
        require $this->template;
        $html = ob_get_clean();
        
        if ($this->chargement_demarrage) {
            if ($this->json) {
                $tableau_json = array('html' => $html, 'script' => $script);
                echo json_encode($tableau_json);
            } else {
                echo $html.'<script type="text/javascript">'.$script.'</script>';
            }
        } else {
            echo $html;
        }
    }

	/**
     * Génération automatique du filtre-liste suivant les paramètres passés
     * @param array $params : Les paramètres d'initialisation du filtre
     */
    public static function FiltrerListe($params)
    {
        if(!isset($params['colonnes'])) new Error('Paramètre "colonnes" manquant dans le filtre');
        $params['classe'] = get_called_class();
        $filtre = new FiltreListe($params);
        $filtre->afficher();
    }
    
    protected function initialiserAttributs ($params)
    {
        $this->unique_id = uniqid();
        $this->tableau_champs = $params['colonnes'];
        $this->tableau_champs_xls = $params["colonnes"];
        foreach ($params as $libelle => $valeur) { 
            $this->$libelle = $valeur;
        }
    }
    
    public function afficheFiltre($champ)
    {
        if (isset($champ['type']) && ($champ['type']=='icone' || $champ['type']=='bouton' || $champ['type']=='champ_libre')) {
            return false;
        }

        $activer_filtrage = true;
        if (isset($champ['activer_filtrage'])) {
            $activer_filtrage = $champ['activer_filtrage'];
        }

        $combo = "";
        if (isset($champ['combo']) || isset($champ['combo_in'])) {
            if (isset($champ['combo'])) $combo = $champ['combo'];
            if (isset($champ['combo_in'])) $combo = $champ['combo_in'];
        }
        
        $identifiant = "";
        if (isset($champ['id'])) {
            $identifiant = 'id="'.$champ['id'].'"';
        }

        $valeur_par_defaut = '';
        if (isset($champ['filtre_defaut'])) {
            $valeur_par_defaut = $champ['filtre_defaut'];
        }
        if (isset($_SESSION['filtres_enregistres'][$this->nom_filtre][$champ['nom']])) {
            $valeur_par_defaut = $_SESSION['filtres_enregistres'][$this->nom_filtre][$champ['nom']];
        }

        if ($combo == "") {

            if ($champ['nom'] == "COCHE") {

                //case à cocher
                echo '<div style="width:100%;padding-left:1px;padding-top:3px;" align="center">';
                // Si cliquer sur la case lance le filtre (par défaut)
                if ($activer_filtrage) {
                    echo '<input  type="checkbox" name="filtre['.$champ['nom'].']" value="1" onclick="lancerFiltre(\''.$this->unique_id.'\')" />';
                } else {
                    echo '<input  type="checkbox" name="filtre['.$champ['nom'].']" value="1" />';
                }
                echo "</div>";

            } else {

                $classe = '';
                if (isset($champ['type'])) {
                    switch ($champ['type']) {
                        case 'date':
                            $classe = 'date';
                            break;
                        default:
                    }
                }

                //champ texte
                echo '<input class="filtre_input '.$classe.'" '.$identifiant.' type="text" name="filtre['.$champ['nom'].']" onkeyup="lancerFiltre(\''.$this->unique_id.'\')" value="'.$valeur_par_defaut.'"/>';
            }

        } else {
            //combo
            echo '<select name="filtre['.$champ['nom'].']" '.$identifiant.' onchange="lancerFiltre(\''.$this->unique_id.'\')" class="filtre_select" />';
            foreach ($combo as $option) {
                $selected = "";
                if ($valeur_par_defaut == $option['valeur']) {
                    $selected = 'selected="selected"';
                } elseif (isset($option['defaut']) && $option['defaut'] == '1') {
                    $selected = 'selected="selected"';
                }
                echo '<option '.$selected.' value="'.$option['valeur'].'">'.$option['texte'].'</option>';
            }
            echo '</select>';
        }
    }
}
