<?php
/**
 * Définit les images pour le site
 * @version 1.0
 **/
class Images
{
    public static function headerLeft($ident = 'header_left')
    {
        return "<a href='/home/index'><img src='/images/interface/logo-afpif-2012-pour fond blanc.png' alt='logos' id='$ident' style='height: 60px;' /></a>";
    }
    public static function barreMenu($ident = 'barre_menu')
    {
        return "<img src='images/interface/barre_menu.png' alt='barre' id='$ident' />";
    }
    public static function deconnexion($ident = 'ico_deconnexion')
    {
        return "<img src='/images/icones/btn_deconnexion_off.png' class='bouton' alt='deconnexion' id='$ident' style='cursor:pointer' />";
    }
    public static function separationSousMenu()
    {
        return "<img src='images/interface/separation_sous_menu.png' alt='separation_sous_menu' />";
    }
    public static function sousMenuImageDroite()
    {
        return "<img src='images/interface/sous_menu_img_droite.png' alt='sous_menu_img_droite' />";
    }
    public static function interrogation($ident = "point_interrogation")
    {
        return '<img src="images/interface/interrogation.jpg" alt="point interrogation" id="'.$ident.'" style="cursor:pointer" />';
    }
    public static function traitementEnCours($ident = "traitement_en_cours", $margin = "0px")
    {
        return '<div><img style="width:25px;height:25px;margin:'.$margin.'" src="/images/application/loader.gif" alt="traitement en cours" id="'.$ident.'" /></div>';
    }

    public static function chargementEnCours($ident = "chargement_en_cours")
    {
        return '<img src="/images/application/loader.gif" alt="traitement en cours" id="'.$ident.'" /> Traitement en cours...';
    }

/**
 * icones
 */
    public static function oui($title = "", $style = "")
    {
        return '<img src="/images/icones/accept.png" alt="oui" title="'.$title.'" style="'.$style.'"/>';
    }
    public static function non($title = "", $style = "")
    {
        return '<img src="/images/icones/delete.png" alt="non" title="'.$title.'" style="'.$style.'"/>';
    }
    public static function alerte($title = "", $style = '')
    {
        return "<img src='/images/icones/exclamation.png' id='img_alert' alt='".$title."' title='".$title."' style='".$style."'/>";
    }
    /*
     * couleurs disponibles :
     *  @param $flag : red, blue, green, orange, pink, purpule, yellow
     *  @param $title
     */
    public static function flag($flag = 'red', $title = "")
    {
        return '<img src="/images/icones/flag_'.$flag.'.png" alt="'.$title.'" title="'.$title.'"/>';
    }

    public static function groupGo($title = "", $onclick = "")
    {
        return '<img src="/images/icones/group_go.png" alt="'.$title.'" title="'.$title.'"  onclick="'.$onclick.'" />';
    }

    /**
    * gestion des icones de fichiers
    */

    public static function icone($nom_icone, $alt, $style = "width:32px")
    {
        return "<img src='/images/icones/icones-fatcow/32x32/".$nom_icone.".png' alt=\"".$alt."\" title=\"".$alt."\" style='".$style."'/>";
    }
    public static function iconeExcel($onclick = "", $title = "XLS", $style = "")
    {
        return "<img src='/images/icones/page_white_excel.png' alt='excel' style=\"".$style."\" title=\"".$title."\" onclick=\"".$onclick."\" />";
    }
    public static function iconeWord()
    {
        return "<img src='images/icones/icone_word.gif' alt='word' />";
    }
    public static function iconePDF($onclick = "", $title = "PDF", $style = "width:16px")
    {
        return "<img src='/images/icones/icones-fatcow/32x32/file_extension_pdf.png' alt='PDF' style=\"".$style."\" title=\"".$title."\" onclick=\"".$onclick."\" />";
    }
    public static function iconeBlocNote()
    {
        return "<img src='images/icones/icone_bloc_note.gif' alt='bloc_note' />";
    }
    public static function iconeImage()
    {
        return "<img src='images/icones/icone_image.png' alt='image' />";
    }
    public static function iconeInconnu()
    {
        return "<img src='images/icones/icone_inconnu.gif' alt='inconnu' />";
    }

    public static function iconeSauvegarder($onclick = "", $title = "Enregistrer", $style = "")
    {
        return "<img src='/images/icones/accept.png' alt='Enregistrer' style='cursor: pointer;".$style."' title=\"".$title."\" onclick=".$onclick." />";
    }
    public static function iconeSauvegarderGros($onclick = "", $title = "Enregistrer", $style = "")
    {
        return "<img src='/images/icones/icones-fatcow/32x32/disk.png' alt='Enregistrer' style='cursor: pointer; width:25px;".$style."' title=\"".$title."\" onclick=".$onclick." />";
    }

    public static function iconeAjouter($onclick = "", $title = "Ajouter", $style = "")
    {
        return "<img src='/images/icones/add.png' alt='Ajouter' style='cursor: pointer;".$style."'  title=".$title." onclick=".$onclick." />";
    }
    public static function iconeAjouterGros($onclick = "", $title = "Ajouter", $style = "")
    {
        return "<img src='/images/icones/icones-fatcow/32x32/add.png' alt='Ajouter' style='cursor: pointer; width:25px;".$style."'  title='".$title."' onclick=".$onclick." />";
    }
    public static function iconeRechercher($onclick = "", $title = "Ajouter", $style = "")
    {
        return "<img src='/images/icones/icones-fatcow/32x32/find.png' alt='Ajouter' style='cursor: pointer; width:25px;".$style."'  title=".$title." onclick=".$onclick." />";
    }

    public static function iconeReinitialiser($onclick = "", $title = "Reinitialiser", $style = "")
    {
        return "<img src='/images/icones/arrow_rotate_clockwise.png' alt='Reinitialiser' style='cursor: pointer;".$style."'  title=".$title." onclick=".$onclick." />";
    }
    public static function iconeReinitialiserGros($onclick = "", $title = "Reinitialiser", $style = "")
    {
        return "<img src='/images/icones/icones-fatcow/32x32/arrow_rotate_clockwise.png' alt='Reinitialiser' style='cursor: pointer; width:25px;".$style."'  title=".$title." onclick=".$onclick." />";
    }

    public static function iconeSupprimer($onclick = "", $title = "Supprimer")
    {
        return "<img src='/images/icones/cross.png' alt='Reinitialiser' style='cursor: pointer;'  title=".$title." onclick=".$onclick." />";
    }

    public static function iconeNonValide($onclick = "", $title = "Non Valide")
    {
        return '<img src="/images/icones/exclamation.png" alt="Non Valide" title="'.$title.'" onclick="'.$onclick.'" />';
    }

    public static function iconeLoupe($onclick = "", $title = "Zoom", $style = "")
    {
        return '<img src="/images/icones/icones-fatcow/32x32/magnifier.png" alt="Zoom" style="cursor: pointer; width:25px;'.$style.'" title="'.$title.'" onclick="'.$onclick.'" />';
    }

    public static function iconeUser($onclick = "", $title = "Accès Fiche", $style = "")
    {
        return '<img src="/images/icones/icones-fatcow/32x32/user.png" alt="Accès Fiche" style="cursor: pointer; width:25px;'.$style.'" title="'.$title.'" onclick="'.$onclick.'" />';
    }
    
    public static function iconeCarte($onclick = "", $title = "Générer la carte étudiant", $style = "")
    {
        return '<img src="/images/icones/icones-fatcow/32x32/vcard.png" alt="Générer la carte étudiant" style="cursor: pointer; width:25px;'.$style.'" title="'.$title.'" onclick="'.$onclick.'" />';
    }

    public static function iconeEditerBordereau($onclick = "", $title = "Editer", $style = "")
    {
        return '<img src="/images/icones/report_edit.png" alt="Editer" style="cursor:pointer; '.$style.'" title="'.$title.'" onclick="'.$onclick.'" />';
    }

    public static function iconeEnvoyerMail($onclick = "", $title = "Envoyer un mail", $style = "width:25px;")
    {
        return '<img src="/images/icones/icones-fatcow/32x32/email.png" alt="Envoyer un mail" style="cursor:pointer; '.$style.'" title="'.$title.'" onclick="'.$onclick.'" />';
    }
    public static function iconeModifierDocument($onclick = "", $title = "Modifier", $style = "width:25px;")
    {
        return '<img src="/images/icones/folder_explore.png" alt="Modifier" style="cursor:pointer; '.$style.'" title="'.$title.'" onclick="'.$onclick.'" />';
    }
    public static function iconeAjoutTemps($onclick = "", $title = "Ajouter temps", $style = "width:25px;")
    {
        return '<img src="/images/icones/icones-fatcow/32x32/time_add.png" alt="Modifier" style="cursor:pointer; '.$style.'" title="'.$title.'" onclick="'.$onclick.'" />';
    }

    /**
     * onglets
     */
    /*
        public static function ongletDetails($id='ong_details', $class = 'onglet')
        {
            return "<img src='images/interface/btn_details_off.gif' alt='Détails' class='$class' id='$id' />";
        }
        public static function ongletEdition($id='ong_edition', $class = 'onglet')
        {
            return "<img src='images/interface/btn_edition_off.png' alt='Edition' class='$class' id='$id' />";
        }
        public static function ongletOtsAssocies($id='ong_ots_associes', $class = 'onglet')
        {
            return "<img src='images/interface/btn_ots_off.png' alt='Ots associés' class='$class' id='$id' />";
        }
        public static function ongletFinal()
        {
            return "<img src='images/interface/sous_menu_img_droite.png' id='onglet_bout' alt='' />";
        }
        public static function ongletSeparateur()
        {
            return "<img src='images/interface/separation_sous_menu.png' alt='' />";
        }
    */

    /**
     * arborescence
     */
    public static function flecheBas()
    {
        return "<img src='images/interface/fleche.png' alt='fleche' />";
    }
    public static function flecheHaut()
    {
        return "<img src='images/interface/fleche_h.png' alt='fleche' />";
    }
    
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
}
