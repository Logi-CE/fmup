<?php
/**
* Définit les boutons images pour le site
**/
class Boutons
{
    /**
    * menu principal
    */
    public static function accueil($lien, $id = 'btn_accueil')
    {
        return "<a href='$lien'><img src='/images/interface/btn_accueil_off.png' alt='Accueil' class='bouton_menu' id='$id' /></a>";
    }
    public static function admin($id = 'btn_admin')
    {
        return "<img src='/images/interface/btn_admin_off.png' alt='Admin' class='bouton_menu' id='$id' />";
    }

    /**
     * Retourne un bouton aide avec un titre et un text
     * @param {String} $title
     * @param {String} $text
     * @return {String}
     */
    /*static function help($title, $text, $class = '') {
      return <<<HTML
        <div class="_blink tooltip $class" title="$title::$text">
          <img src="/images/lib/tooltips/help.png" alt="help" />
        </div>
    HTML;
    }*/
    public static function help($title, $text, $class = '')
    {
        //replacement des < et > par des parentheses pour la validation w3c
        $search = array ('<', '>');
        $replace = array ('((', '))');
        $text = str_replace($search, $replace, $text);
        return <<<HTML
    <img class="_blink tooltip $class" src="/images/lib/tooltips/help.png" alt="help" title="$title::$text" />
HTML;
    }
    public static function grosHelp($title, $text, $class = '')
    {
        return <<<HTML
<div class="_blink gros_tooltip $class" title="$title::$text">
  <img src="/images/lib/tooltips/help.png" />
</div>
HTML;
    }

    /**
    * Boutons classiques
    */

    public static function RetourListe($lien, $id = 'btn_retour')
    {
        // Pour la conservation des filtres
        if (substr_count($lien, "?")) {
            $lien .= "&from_retour=1";
        } else {
            $lien .= "?from_retour=1";
        }
        return "<img src='/images/boutons/btn_retour_off.png' alt='Retour à la liste' class='bouton' id='$id' onclick=\"window.location.href='$lien'\"/>";
    }

    public static function bloquer ($id = "btn_bloquer", $type = "gros_texte")
    {
        if ($type=="gros_texte") {
            return "<img src='/images/boutons/btn_bloquer_off.png' alt='Bloquer' class='bouton' id='$id' />";
        } else {
            return "<img src='/images/boutons/btn_bloquer2_off.png' alt='Bloquer' class='bouton' id='$id' />";
        }
    }
    public static function retour($lien, $id = 'btn_retour')
    {
        return "<a href='$lien'><img src='/images/boutons/btn_retour_off.png' alt='Retour' class='bouton' id='$id' /></a>";
    }

    public static function nouveau($lien, $id = 'btn_nouveau')
    {
        return '<a href="'.$lien.'"><img src="/images/boutons/'.$id.'_off.png" alt="Enregistrer un nouvel élement" class="bouton" id="'.$id.'" /></a>';
    }
    public static function annuler($lien, $id = 'btn_annuler')
    {
        return "<a href='$lien'><img src='/images/boutons/btn_annuler_off.png' alt='Annuler' class='bouton' id='$id' /></a>";
    }
    public static function exporter($id = 'btn_exporter')
    {
        return "<img src='/images/boutons/btn_exporter_off.png' alt='Exporter sous Excel' class='bouton' id='$id' />";
    }

    public static function enregistrerEtNew($id = 'btn_enregistrer_et_new')
    {
        return "<img src='/images/boutons/btn_enregistrer_et_new_off.png' alt='Enregistrer l'élement et création d'un suivant' class='bouton' id='$id' />";
    }

 public static function enregistrer($id = 'btn_enregistrer')
    {
        //return "<img src='/images/boutons/btn_enregistrer_off.png' alt='Enregistrer un nouvel élement' class='bouton' id='$id' />";
        if(Config::getIsMultilingue()){
            $libelle = Langue::display('G_ENREGISTRER');
            $title   = Langue::display('G_ENREGISTRER_NOUVEL_EVENEMENT');
        }else{
            $libelle = "Enregistrer";
            $title   = "Enregistrer un nouvel élement";
        }
        $apram = array(
                      'libelle'=> $libelle,
                      'width'  => '110',
                      'id'     => $id,
                      'title'  => $title,
                      'image'  =>'disk.png'
                  );
        return self::generique($apram);
    }
    public static function exporterExcel($script="", $id = 'btn_exportr_excel')
    {
        $apram = array(
                      'libelle'=>'Exporter',
                      'width'=>'110',
                      'id'=>$id,
                      'title'=>'Exporter le tableau sous Excel',
                      'image'=>'icones-fatcow/32x32/table_excel.png',
                      'script'=>'onClick="document.location.href=\''.$script.'?exporter=xls\'"',
                  );
        return self::generique($apram);
    }

    public static function valider($id = 'btn_valider')
    {
        return "<img src='/images/boutons/btn_valider_off.png' alt='Valider' class='bouton' id='$id' />";
    }
    public static function reactiver($lien, $id = 'btn_reactiver')
    {
        return "<a href='$lien'><img src='/images/boutons/btn_reactiver_off.png' alt='Réactiver' class='bouton' id='$id' /></a>";
    }

    public static function supprimer($id = 'btn_supprimer')
    {
        return "<img src='/images/boutons/btn_supprimer_off.png' alt='Supprimer' title='Supprimer' class='bouton' id='$id' />";
    }

    public static function supprimerBouton($id = 'btn_supprimer')
    {
        return "<input type='button' value='Supprimer' style='cursor:pointer;' id='$id' name='$id' />";
    }

    public static function nonSupprimable($id = 'btn_non_supprimable')
    {
        return "<img src='/images/boutons/btn_supprimer_off.png' alt='Non supprimable' title='Non supprimable' class='bouton' id='$id' />";
    }

    public static function afficher($id = 'btn_afficher')
    {
        return "<img src='/images/application/nolines_plus.gif' alt='Afficher' class='bouton' style='padding-bottom:0' id='$id' />";
    }
    public static function cacher($id = 'btn_cacher')
    {
        return "<img src='/images/application/nolines_minus.gif' alt='Cacher' class='bouton' style='padding-bottom:0' id='$id' style='display:none;' />";
    }

    public static function prendreEnCompte($id = 'btn_prendreencompte')
    {
        return "<img src='/images/boutons/btn_prendre_en_charge_off.png' alt='Prendre en charge' class='bouton' id='$id' />";
    }

    public static function realiser($id = 'btn_realiser')
    {
        return "<img src='/images/boutons/bt_realiser_off.png' alt='Réaliser' class='bouton' id='$id' />";
    }

    public static function finaliser($id = 'btn_finaliser')
    {
        return "<img src='/images/boutons/bt_finaliser_off.png' alt='Finaliser' class='bouton' id='$id' />";
    }
    public static function editer($id = 'btn_editer')
    {
        return "<img src='/images/boutons/bt_editer_off.png' alt='Editer' class='bouton' id='$id' />";
    }

    public static function alerte($id = 'btn_alerte')
    {
        return "<img src='/images/application/alerte.png' alt='Afficher' style='width:11px' class='bouton icone_alerte' id='$id' />";
    }

    public static function telechargerPDF($liens = '', $id = 'telecharger_pdf')
    {
        return "<a href='$liens'><img src='/images/boutons/btn_telecharger_pdf_off.png' alt='T&eacute;l&eacute;charger le PDF' class='bouton' id='$id' /></a>";
    }

    public static function enregistrerBouton($id = 'btn_enregistrer')
    {
        return "<input type='button'  value='Enregistrer' style='cursor:pointer;' id='$id' name='$id' />";
    }

    public static function genererPDF($liens = '', $id = 'telecharger_pdf')
    {
        return "<a href='$liens'><img src='/images/boutons/btn_telecharger_pdf_off.png' alt='T&eacute;l&eacute;charger le PDF' class='bouton' id='$id' /></a>";
    }

    public static function genererWord($liens = '', $id = 'telecharger_word')
    {
        return "<a href='$liens'><img src='/images/boutons/btn_word_off.png' alt='T&eacute;l&eacute;charger le document word' class='bouton' id='$id' /></a>";
    }
    public static function genererExcel($liens = '', $id = 'telecharger_excel')
    {
        return "<a href='$liens'><img src='/images/boutons/btn_excel_off.png' alt='T&eacute;l&eacute;charger le document excel' class='bouton' id='$id' /></a>";
    }

    public static function genererIconeWord($liens = '', $id = 'telecharger_word')
    {
        return "<a href='$liens'><img src='/images/icones/icone_word.gif' alt='T&eacute;l&eacute;charger le document word' class='bouton' id='$id' /></a>";
    }
    public static function genererIconeExcel($liens = '', $id = 'telecharger_excel')
    {
        return "<a href='$liens'><img src='/images/icones/icone_excel.gif' alt='T&eacute;l&eacute;charger le document excel' class='bouton' id='$id' /></a>";
    }

    public static function duplicate($liens = '', $id = 'btn_duplicate')
    {
        return "<a href='$liens'><img src='/images/boutons/btn_duplication_off.png' alt='Duplication' class='bouton' id='$id' /></a>";
    }

    /*  exmeple paramétrage
     * $apram = array(
     * 				'libelle'=>'mon bouton',
     * 				'script'=>'onclick="mon_action()"',
     * 				'width'=>'150',
     * 				'style'=>'border:1px',
     * 				'image'=>'icone.png',
     * 				'id'=>'id_bouton',
     * 			);
     */
    public static function generique($params)
    {
        $script="";
        $width="width: 100px;";
        $image="";
        $style="";
        $title="";
        $id = 'btn_'.str_replace(' ', '_', strtolower($params['libelle']));

        if (isset($params['id'])) {
            $id = $params['id'];
        }
        if (isset($params['script'])) {
            $script=$params['script'];
        }
        if (isset($params['width'])) {
            $mesure = '';
            if (strpos($params['width'], '%') === false && strpos($params['width'], 'px') === false) {
                // si on ne précise pas la mesure, alors par défaut, il s'agit de PX
                $mesure = 'px';
            }
            $width='width:'.$params['width'].$mesure.';';
        }
        if (isset($params['title'])) {
            $title=$params['title'];
        }
        if (isset($params['style'])) {
            $style=';'.$params['style'];
        }
        if (isset($params['image'])) {
            $image= '<img  alt="'.$title.'" style="width:12px;padding-top:2px;float:right;" src="/images/icones/'.$params['image'].'" >&nbsp;&nbsp;';
        }

        $html = '<table id="'.$id.'"  title="'.$title.'" '.$script.' class="bouton_generique_off" style="height:21px; text-align:center;'.$width.$style.'" >';
        $html .= '<tr>';
        $html .= '<td class="Boutondebut" >'.$image.'</td>';
        $html .= '<td class="bouton_generique_libelle" >'.$params['libelle'].'</td>';
        $html .= '<td class="Boutonfin" ></td>';
        $html .= '</tr>';
        $html .= '</table>';
        /*
        $html = '<div id="'.$id.'" class="bouton_generique_off" style="'.$width.$style.'" '.$script.'  >';
        $html .='<span class="bouton_generique_libelle" style="float:left;" title="'.$title.'">'.$params['libelle'].'</span>';
        $html .= '</div>';
        */
        return $html;
    }

    //
    //		static function duplicate($id = 'btn_duplicate'){
    //			return "<input type='button'  value='Duplication.' style='cursor:pointer;' id='$id' name='$id' />";
    //		}

    /*****************************
    * petites icones cliquables *
    *****************************/
    public static function iconeSupprimer($id = 'icone_supprimer', $class = '', $script = '')
    {
        return "<img src='/images/icones/cross.png' alt='Supprimer' title='Supprimer' id='$id' class='icone_cliquable icone_supprimer $class' $script />";
    }
    public static function iconeRefresh($id = 'icone_refresh', $class = '', $script = '')
    {
        return "<img src='/images/icones/arrow_refresh.png' alt='Réinitialiser' title='Réinitialiser' id='$id' class='icone_cliquable $class' $script />";
    }
    public static function iconeChanger($id = 'icone_changer')
    {
        return "<img src='/images/application/modifier.gif' alt='Changer' title='Changer la valeur' id='$id' class='icone_cliquable' />";
    }
    public static function iconeValider($id = 'icone_valider')
    {
        return "<img src='/images/application/valider.gif' alt='Valider' title='Enregistrer' id='$id' class='icone_cliquable icone_valider' />";
    }
    public static function iconeAnnuler($id = 'icone_annuler')
    {
        return "<img src='/images/application/img_supprimer.gif' alt='Annuler' title='Annuler' id='$id' class='icone_cliquable' />";
    }
    public static function iconeAjouter($title = 'Ajouter', $id = 'icone_ajouter')
    {
        return "<img src='/images/application/add.png' alt='$title' title='$title' id='$id' class='icone_cliquable' />";
    }
    public static function iconeOuvrirPopup($title = 'Sélectionner', $id = 'icone_ajouter')
    {
        return "<img src='/images/application/ouvrir_popup.jpg' alt='$title' title='$title' id='$id' class='icone_cliquable' />";
    }

    public static function enregistrerEtValider ($id = 'btn_enregistrer_valider')
    {
        return "<img src='/images/boutons/btn_enregistrer_et_valider_off.png' alt='Enregistrer et valider' class='bouton' id='$id' />";
    }

    public static function consulterModification ($id = 'btn_consulter_modification')
    {
        return "<img src='/images/boutons/btn_consulter_modifications_off.png' alt='Consulter les modifications' class='bouton' id='$id' />";
    }

    public static function fermer($id = 'btn_fermer')
    {
        return "<img src='/images/boutons/btn_fermer_off.png' alt='Fermer' class='bouton' id='$id' />";
    }

    public static function lancerScript($id = 'btn_lancer_script')
    {
        return "<img src='/images/boutons/btn_lancer-le-script_off.png' alt='Lancer le script' class='bouton' id='$id' />";
    }
    public static function validerScript ($id = "btn_valider_script")
    {
        return "<img src='/images/boutons/btn_valider-le-script_off.png' alt='Valider le script' class='bouton' id='$id' />";
    }
    public static function nouvelIncident ($lien, $id = "btn_nouveau")
    {
        return "<a href='".$lien."'><img src='/images/boutons/btn_nouvel-incident_off.png' alt='Nouvel incident' class='bouton' id='$id' /></a>";
    }
    /*static function retour ($id = "btn_retour") {
      return "<img src='/images/boutons/btn_retour_off.png' alt='Retour' class='bouton' id='$id' />";
    }*/
    public static function quitter ($id = "btn_quitter")
    {
        return "<img src='/images/boutons/btn_quitter_off.png' alt='Quitter' class='bouton' id='$id' />";
    }
    public static function refuser ($id = "btn_refuser")
    {
        return "<img src='/images/boutons/btn_refuser_off.png' alt='Refuser' class='bouton' id='$id' />";
    }
    public static function scinder ($id = "btn_scinder")
    {
        return "<img src='/images/boutons/btn_scinder_off.png' alt='Scinder la commande' class='bouton' id='$id' />";
    }

    public static function trois_points ($params)
    {
        $defaut_parametres = array('class'=>'trois_points');

        //fusion des attributs communs
        foreach ($defaut_parametres as $cle => $param) {
            if (isset($params[$cle])) {
                $params[$cle] .= $defaut_parametres[$cle];
            } else {
                $params[$cle] = $defaut_parametres[$cle];
            }
        }

        $html_attributs = "";
        foreach ($params as $cle => $param) {
            $html_attributs .= ' '.$cle.'="'.$param.'"';
        }

        return '<img src="/images/boutons/btn_3pt_off.png" alt="..." '.$html_attributs.' />';
    }

    public static function ajouterDocument($id = "btn_ajouter_document", $class = "", $title = "", $avec_image = true)
    {
        if ($title) {
          $title='title="' . $title.'"';
        }
        if ($avec_image) {
            return "<img src='/images/boutons/btn_ajouter_document_off.png' alt='Ajouter un document' $title class='bouton $class' id='$id' />";
        } else {
            return "<input type='button'  value='Ajouter un document' style='cursor:pointer;' $title id='$id' name='$id' />";
        }
    }
    public static function ajouterFacture($id = "btn_ajouter_facture", $class = "", $title = "")
    {
        if ($title) {
            $title='title="'.$title.'"';
        }
        return "<input type='button'  value='Ajouter une Facture' style='cursor:pointer;' $title id='$id' name='$id' />";
    }
    public static function majAllFactures($id = "btn_maj_all_facture", $class = "", $title = "Mettre à jour toutes les factures")
    {
        if ($title) {
            $title='title="'.$title.'"';
        }
        return "<input type='button'  value='MàJ de toutes les factures' style='cursor:pointer;' $title id='$id' name='$id' />";
    }
    public static function annulerValidation($id = 'btn_annuler_validation')
    {
        return '<img src="/images/boutons/btn_annuler_validation_off.png" alt="Annuler Validation" class="bouton" id="'.$id.'" />';
    }

    public static function envoyerMail($id = 'btn_envoyerMail')
    {
        return '<img src="/images/boutons/btn_envoyermail_off.png" alt="Envoyer mail" class="bouton" id="'.$id.'" />';
    }

    public static function renouveler($id = 'btn_renouveler')
    {
        return '<img src="/images/boutons/btn_renouveler_off.png" alt="A renouveler" class="bouton" id="'.$id.'" />';
    }

    public static function deBloquer($id = 'btn_de_bloquer')
    {
        return '<img src="/images/boutons/btn_debloquer_off.png" alt="Débloquer" title="Débloquer" class="bouton" id="'.$id.'" />';
    }

    public static function btnconfirmer ($params)
    {
        $id = "";
        $script = "";
        $trad = "Confirmer";

        if (isset($params['id'])) {
            $id = $params['id'];
        }
        if (isset($params['trad'])) {
            $trad=$params['trad'];
        }

        return '<button type="submit" class="positive" name="save" '.$id.'><img src="/images/boutons/apply2.png" alt=""/>'.$trad.'</button>';
    }
    public static function btnDefault ($params){
        $id = "";
        $link = "#";
        $script = "";
        $img = '';
        $trad = "Envoyer";
        //class : regular - positive - negative
        $class = "positive";

        if (isset($params['id'])) {
            $id = $params['id'];
        }
        if (isset($params['link'])) {
            $link = $params['link'];
        }
        if (isset($params['script'])) {
            $script = 'onclick='.$params['script'];
        }
        if (isset($params['trad'])) {
            $trad = $params['trad'];
        }
        if (isset($params['class'])) {
            $class = $params['class'];
        }
        if (isset($params['img'])) {
            $img = "<img src='".$params['img']."' style='width:20px;height:20px'/>";
        }

        return '<a href="'.$link.'" '.$script.' class="'.$class.'" '.$id.'>'.$img.''.$trad.'</a>';
    }
    public static function btnAnnuler ($params)
    {
        $id = "";
        $script = "";
        $trad = "Annuler";

        if (isset($params['id'])) {
            $id = $params['id'];
        }
        if (isset($params['script'])) {
            $script=$params['script'];
        }
        if (isset($params['trad'])) {
            $trad=$params['trad'];
        }

        return '<a href="'.$script.'" class="negative" '.$id.'><img src="/images/boutons/cross.png" alt=""/>'.$trad.'</a>';
    }
    public static function btnSkip ($params){
        $id = "";
        $script = "";
        $trad = "Skip";

        if (isset($params['id'])) {
            $id = $params['id'];
        }
        if (isset($params['script'])) {
            $script=$params['script'];
        }
        if (isset($params['trad'])) {
            $trad=$params['trad'];
        }

        return '<a href="'.$script.'" class="regular" '.$id.'>'.$trad.'</a>';
    }
}
