<?php

class CtrlListe extends Controller
{
    public function Lister()
    {
        if (isset($_REQUEST['xls'])) {
            header("Content-Type: application/vnd.xls; name=\"export.xls\"");
            header("Content-Disposition: inline; filename=\"export.xls\";");
            echo     "<html>
                    <head><meta http-equiv=\"Content-Type\" content=\"text/html; \" /></head>
                    <body>
                    <table border=\"1\">";
        }

        if (isset($_REQUEST['unique_id'], $_SESSION[$_REQUEST['unique_id']])) {

            // Gestion de la purge de la session
            // Les formulaires étant enregistrés en session ils sont constamment présent et alourdissent la mémoire
            // Il faut donc gérer une date de modification de session et une expiration
            foreach ($_SESSION as $cle => $session) {
                // Ce serait bête de perdre le filtre courant
                if ($cle != $_REQUEST['unique_id']) {
                    if (is_array($session) && isset($session['filtre']) && (empty($session['timestamp_expiration']) || $session['timestamp_expiration'] < time())) {
                        unset($_SESSION[$cle]);
                    }
                // Mise à jour de la date d'expiration
                } else {
                    $_SESSION[$cle]['timestamp_expiration'] = time() + Config::getTimeoutSessionId();
                }
            }

            $filtrer = true;

            $classe = $_SESSION[$_REQUEST['unique_id']]['classe'];
            if (isset($_REQUEST['xls'])) {
                $tableau_champs = $_SESSION[$_REQUEST['unique_id']]['tableau_champs_xls'];
            } else {
                $tableau_champs = $_SESSION[$_REQUEST['unique_id']]['tableau_champs'];
            }
            $clef = $_SESSION[$_REQUEST['unique_id']]['clef'];
            $filtre = $_SESSION[$_REQUEST['unique_id']]['filtre'];
            $unite = $_SESSION[$_REQUEST['unique_id']]['unite'];
            $icones = $_SESSION[$_REQUEST['unique_id']]['icones'];

            $filtre_complementaire = array();
            if (isset($_SESSION[$_REQUEST['unique_id']]['filtre_complementaire']) && $_SESSION[$_REQUEST['unique_id']]['filtre_complementaire']<>'') {
                $filtre_complementaire = $_SESSION[$_REQUEST['unique_id']]['filtre_complementaire'];
            }

            // gestion des particularités de certaines listes
            $options_supplementaires = (isset($_SESSION[$_REQUEST['unique_id']]['options_supplementaires'])) ?  $_SESSION[$_REQUEST['unique_id']]['options_supplementaires']  :  array();

            // Gestion de la requête spécifique si il y en a une
            $requete_specifique = (isset($_SESSION[$_REQUEST['unique_id']]['requete_specifique'])) ?  $_SESSION[$_REQUEST['unique_id']]['requete_specifique']  :  "";

            // on peut demander à ce que la gestion du click ligne ne fasse pas changer de page, mais en ouvre une autre:
            // c'est un bouleen :
            //         - si TRUE  --> window.open
            //         - si FALSE --> window.location.href
            $ouverture_nouvelle_page =  (isset($_SESSION[$_REQUEST['unique_id']]['ouverture_nouvelle_page_si_click'])) ?  $_SESSION[$_REQUEST['unique_id']]['ouverture_nouvelle_page_si_click']  :  false;

            $ordre = "";
            $destinationCtrl = $_SESSION[$_REQUEST['unique_id']]['destinationCtrl'];
            $clef_destination_ctrl = $_SESSION[$_REQUEST['unique_id']]['clef_destination_ctrl'];
            if (isset($_REQUEST['ordre_'.$_REQUEST['unique_id']])) {
                $ordre = $_REQUEST['ordre_'.$_REQUEST['unique_id']];
            }

            // Gestion du TOP
            $top = 0;
            if (!empty($_REQUEST['top'])) {
                $top = $_REQUEST['top'];
            }
            // Gestion de la pagination
            $limit = '';
            $numero_page = $_REQUEST['numero_page'];


            //pas de limite de ligne si export xls
            if (isset($_REQUEST['xls'])) {
                $top = $_REQUEST['nb_elements_total'];
                $numero_page = 1;
            }

            // Gestion du paramètre "sans limite"
            if ($top != 0) {
                $limit = (($numero_page - 1) * $top).','.$top;
            }
            
            $haut = 0;

            //entete du tableau xls
            if (isset($_REQUEST['xls'])) {
                echo '<tr>';

                foreach ($tableau_champs as $champ) {

                    $libelle = '';
                    if (isset($champ['libelle'])) {
                        $libelle = $champ['libelle'];
                    }
                    $nom = $champ['nom'];

                    $export_xls = true;
                    if (isset($champ['export_xls'])) {
                        $export_xls = $champ['export_xls'];
                    }

                    echo '<td style="background-color: #9CF;font-weight:bold;" align="center">'.$libelle.'</td>';

                }
                echo '</tr>';
            }

            // ATTENTION : utilisaer la requête spécifique enlève la possibilité de filtrer sur la liste pour le moment
            if ($requete_specifique != "") {
                $filtre = array();
                if (isset($_REQUEST['filtre'])) {
                    // Méthode pour un filtrage particulier
                    if (method_exists($classe, 'filtrageListes')) {
                        $filtre = $classe::filtrageListes($_REQUEST['filtre'], $filtre);
                    }

                    $tableau_filtres = $tableau_champs;
                    if (!empty($filtre_complementaire)) {
                        $tableau_filtres = array_merge($tableau_champs, $filtre_complementaire);
                    }
                    foreach ($tableau_filtres as $champ) {
                        if (!isset($champ['activer_filtrage']) || $champ['activer_filtrage']) {
                            if (isset($_REQUEST['filtre'][$champ['nom']])) {
                                if ($_SESSION[$_REQUEST['unique_id']]['nom_filtre']) {
                                    $_SESSION['filtres_enregistres'][$_SESSION[$_REQUEST['unique_id']]['nom_filtre']][$champ['nom']] = $_REQUEST['filtre'][$champ['nom']];
                                }
                                Console::enregistrer($champ);
                                if ($_REQUEST['filtre'][$champ['nom']] != "") {
                                    if (isset($champ["type"]) && $champ["type"] == 'date') {
                                        $filtre[$champ['nom']] = $champ['nom']." LIKE '%".Sql::sanitize(Date::frToUk($_REQUEST['filtre'][$champ['nom']]))."%' ";
                                    } elseif (isset($champ["combo_in"])) {
                                        $filtre[$champ['nom']] = $champ['nom']." IN ('".str_replace(",", "','", Sql::sanitize($_REQUEST['filtre'][$champ['nom']]))."') ";
                                    } elseif (isset($champ["combo"])) {
                                        $filtre[$champ['nom']] = $champ['nom']." = '".Sql::sanitize($_REQUEST['filtre'][$champ['nom']])."' ";
                                    } else {
                                        $filtre[$champ['nom']] = $champ['nom']." LIKE '%".Sql::sanitize($_REQUEST['filtre'][$champ['nom']])."%' ";
                                    }

                                } elseif (isset($champ['obligatoire'])) {
                                    $filtrer = false;
                                }
                            }
                        }
                    }
                }
                $tableau_objets = $classe::executerRequeteSpecifique($requete_specifique, $filtre, $ordre);
                // TODO : Ici gérer le count

                $nb_objets = 0;
                $tableau_colonne = array();
                $flag_paire = false;
            } else {
                if (!$filtre) {
                    $filtre = array();
                }

				$params_connexion = Config::parametresConnexionDb();
                if (isset($_REQUEST['filtre'])) {

                    // Méthode pour un filtrage particulier
                    if (method_exists($classe, 'filtrageListes')) {
                        $filtre = $classe::filtrageListes($_REQUEST['filtre'], $filtre);
                    }

                    $tableau_filtres = $tableau_champs;
                    if (!empty($filtre_complementaire)) {
                        $tableau_filtres = array_merge($tableau_champs, $filtre_complementaire);
                    }
                    foreach ($tableau_filtres as $champ) {
                        if (isset($_REQUEST['filtre'][$champ['nom']])) {
                            if ($_SESSION[$_REQUEST['unique_id']]['nom_filtre']) {
                                $_SESSION['filtres_enregistres'][$_SESSION[$_REQUEST['unique_id']]['nom_filtre']][$champ['nom']] = $_REQUEST['filtre'][$champ['nom']];
                            }
                            if ($_REQUEST['filtre'][$champ['nom']] != "") {
                                 if (isset($champ["type"]) && $champ["type"] == 'date') {
									if ($params_connexion['driver'] == 'mysql') {
										$filtre[$champ['nom']] = $champ['nom']." LIKE '%".Sql::sanitize(Date::frToUk($_REQUEST['filtre'][$champ['nom']]))."%' ";
									} elseif ($params_connexion['driver'] == 'mssql') {
										$filtre[$champ['nom']] = "CONVERT(VARCHAR, ".$champ['nom'].", 103) LIKE '%".Sql::sanitize(Date::frToSql($_REQUEST['filtre'][$champ['nom']]))."%'";
									}
                                 } elseif (isset($champ["combo_in"])) {
                                    $filtre[$champ['nom']] = $champ['nom']." IN ('".str_replace(",", "','", Sql::sanitize($_REQUEST['filtre'][$champ['nom']]))."') ";
                                } elseif (isset($champ["combo"])) {
                                    $filtre[$champ['nom']] = $champ['nom']." = '".Sql::sanitize($_REQUEST['filtre'][$champ['nom']])."' ";
                                } else {
                                    $filtre[$champ['nom']] = $champ['nom']." LIKE '%".Sql::sanitize($_REQUEST['filtre'][$champ['nom']])."%' ";
                                }

                            } elseif (isset($champ['obligatoire'])) {
                                $filtrer = false;
                            }
                        }
                    }
                }

                $tableau_colonne = array();
                $flag_paire = false;

                $options = array();
                $options["order"] = $ordre;
                
                if ($params_connexion['driver'] == 'mysql') {
                    $options["limit"] = $limit;
                } elseif ($params_connexion['driver'] == 'mssql') {
                    $options["top"] = $top;
                    $options["paging"] = array(
                                                "numero_page"=>$numero_page,
                                                "nb_element"=>$top
                                                );
                }
                foreach ($options_supplementaires as $clef_option => $option) {
                    $options[$clef_option] = $option;
                }
                if ($filtrer) {
                    if (!empty($_SESSION[$_REQUEST['unique_id']]['fonction_listage'])) {
                        $fonction_listage = $_SESSION[$_REQUEST['unique_id']]['fonction_listage'];
                    } else {
                        $fonction_listage = 'findAll';
                    }
                    Console::enregistrer($filtre);
                    $tableau_objets = call_user_func(array($classe, $fonction_listage), $filtre, $options);
                } else {
                    $tableau_objets = array();
                }
                if (count($tableau_objets) > 0) {
                    if (isset($tableau_objets[0]->REQUETE_NOMBRE_LIGNE)) {
                        $nb_objets = $tableau_objets[0]->REQUETE_NOMBRE_LIGNE;
                    } elseif (method_exists($classe, 'count')) {
                        $nb_objets = $classe::count($filtre);
                    } else {
                        $nb_objets = 0;
                    }
                } else {
                    $nb_objets = 0;
                }

            }
            $premiere = true;
            if (count($tableau_objets) > 0) {
                $cpt_case = 0;
                $cpt_ligne = 0;
                foreach ($tableau_objets as $instance) {
                    $cpt_ligne++;

                    $left = 0;
                    if (isset($_REQUEST['xls'])) {
                        echo '<tr>';
                    }

                    if ($flag_paire) {
                        $class_ligne = "liste_div_impaire";
                        $class_ligne_off = "bordure liste_div_impaire";
                        $class_ligne_on = "bordure liste_div_impaire ligne_hover";
                    } else {
                        $class_ligne = "liste_div_paire";
                        $class_ligne_off = "bordure liste_div_paire";
                        $class_ligne_on = "bordure liste_div_paire ligne ligne_hover";
                    }

                    $flag_paire = !$flag_paire;

                    foreach ($tableau_champs as $instance->champHTML) {

                        // Méthode d'affichage particulière d'un champ
                        if ($icones) {
                            $valeur = preg_replace('/{{CLEF}}/', $instance->getAttribute($clef), $icones);
                        } elseif (method_exists($classe, 'afficheChamp')) {
                            $valeur = $classe::afficheChamp($instance->champHTML, $instance->getId());
                        }

                        if (isset($instance->champHTML['largeur'])) {
                            $largeur = $instance->champHTML['largeur'];
                        } else {
                            $largeur = '200px';
                        }
                        $nom = $instance->champHTML['nom'];
                        $title = '';
                        $style = '';

                        if ((isset($instance->champHTML['type']) && $instance->champHTML['type'] != 'liste_icones_salle' && $instance->champHTML['type'] != 'champ_libre') || !isset($instance->champHTML['type'])) {
                            $valeur = call_user_func(array($instance, 'get'.String::toCamlCase($nom)));
                        }
                        if (isset($instance->champHTML['liaison'])) {
                            $valeur = call_user_func(array($instance, 'get'.String::toCamlCase($instance->champHTML['liaison'])));
                        }

                        if (isset($_REQUEST['xls'])) {
                            $valeur = utf8_decode($valeur);
                        }

                        //formatage et alignement en fonction du type de colonne
                        if (isset($instance->champHTML['ifnull']) && ($valeur === '' || $valeur == ' ' || $valeur === null)) {
                            $valeur = $instance->champHTML['ifnull'];
                        } elseif (isset($instance->champHTML['type'])) {
                            switch ($instance->champHTML['type']) {
                                case 'int':
                                    $style .= 'text-align: right;';
                                    //$valeur = formatNombre($valeur, 0);
                                    $valeur = number_format($valeur, 0, ',', ' ');
                                    break;
                                case 'float2':
                                    $style .= 'text-align: right;';
                                    //$valeur = formatNombre($valeur, 2);
                                    $valeur = number_format((float) $valeur, 2, ',', ' ');
                                    break;
                                case 'monetaire':
                                    $style .= 'text-align: right;';
                                    //$valeur = formatNombre($valeur, 2);
                                    $valeur = UniteHelper::getFormatMonetaire($valeur);
                                    break;
                                case 'poids':
                                    $style .= 'text-align: right;';
                                    $valeur = UniteHelper::getFormatTonne($valeur, 3, 'kg');
                                    break;
                                case 'icone':
                                    $style .= 'text-align: center;';
                                    $valeur = '<img src="'.$instance->champHTML['src-icone'].'">';
                                    if (isset($instance->champHTML['titre'])) {
                                        $instance->champHTML['titre'] = false;
                                    }
                                    break;
                                case 'date':
                                    $style .= 'text-align: center;';
                                    break;
                                case 'sans_html':
                                        $valeur = strip_tags($valeur);
                                    break;
                                case 'flag':
                                    $style .= 'text-align: center;';
                                    $valeur    = ($valeur)? Images::oui() : Images::non();
                                    if (isset($instance->champHTML['titre'])) {
                                        $instance->champHTML['titre'] = false;
                                    }
                                    break;
                                case 'flag_inverse':
                                    $style .= 'text-align: center;';
                                    $valeur    = ($valeur)? Images::non() : Images::oui();
                                    if (isset($instance->champHTML['titre'])) {
                                        $instance->champHTML['titre'] = false;
                                    }
                                    break;
                                case 'checkbox':
                                    $style .= 'text-align: center;';
                                    $disabled = (isset($instance->champHTML['disabled']) && $instance->champHTML['disabled']) ? 'disabled="disabled"' : '';
                                    $value_check = (isset($instance->champHTML['nom_champ_value'])) ? call_user_func(array($instance, 'get'.String::toCamlCase($instance->champHTML['nom_champ_value']))) : 1;
                                    $valeur = ($valeur) ? '<input class="checkbox_'.$_REQUEST['unique_id'].'_'.$nom.'" type="checkbox" value="'.$value_check.'" checked="checked" '.$disabled.'>': '<input class="checkbox_'.$_REQUEST['unique_id'].'_'.$nom.'" type="checkbox" value="'.$value_check.'" '.$disabled.'>';
                                    break;
                            }
                        }

                        //alignement forcé
                        if (isset($instance->champHTML['alignement'])) {
                            $style .= 'text-align: '.$instance->champHTML['alignement'].';';
                        }

                        //couleur fond
                        if (isset($instance->champHTML['couleur-fond'])) {
                            $style .= 'background-color: '.$instance->champHTML['couleur-fond'].';';
                            //$class_ligne_off = $instance->champHTML['couleur-fond'];
                        }

                        //info bulle
                        if (isset($instance->champHTML['titre']) && $instance->champHTML['titre']===true) {
                            if (isset($instance->champHTML['title'])) {
                                $title .= 'title="'.call_user_func(array($instance, 'get'.String::toCamlCase($instance->champHTML['title']))).'"';
                            } else {
                                $title .= 'title="'.$valeur.'"';
                            }
                        }

                        // Pas de titre dans le cas de l'affichage d'une liste d'icônes !!
                        if (isset($instance->champHTML['type']) && $instance->champHTML['type'] == 'liste_icones_salle') {
                            $title = '';
                        }

                        //lien sur click
                        $flag_lien = false;
                        $datas_complementaires_postees = (!empty($_SESSION[$_REQUEST['unique_id']]['datas_complementaires_postees'])) ?  '&'.$_SESSION[$_REQUEST['unique_id']]['datas_complementaires_postees']  :  "";
                        if (isset($instance->champHTML['lien'])) {
                            // On regarde si des conditions de non sélection ont été données
                            $flag_conditions = false;
                            if (isset($instance->champHTML['tab_conditions_unset'])) {
                                $cpt_conditions = 0;
                                foreach ($instance->champHTML['tab_conditions_unset'] as $champ => $valeur_champ) {
                                    if ($instance->getAttribute($champ) == $valeur_champ) {
                                        $cpt_conditions++;
                                    }
                                }
                                if ($cpt_conditions == count($instance->champHTML['tab_conditions_unset'])) {
                                    $class_ligne .= ' non_selectionnable';
                                    if ($title != '') {
                                        $title = 'title="'.((isset($instance->champHTML['message_unset'])) ? $instance->champHTML['message_unset'] : 'Non sélectionnable').'"';
                                    }
                                    $flag_conditions = true;
                                }
                            }

                            if (!$flag_conditions) {
                                $flag_lien = $instance->champHTML['lien'];
                                if ($flag_lien !== true) {
                                    $flag_lien = "javascript:".$instance->champHTML['lien']."('".$instance->getAttribute($clef)."');";
                                    $flag_lien = "onclick=\"".$flag_lien."\"";
                                } elseif ($destinationCtrl!="") {
                                    if ($clef_destination_ctrl) {
                                        $flag_lien = '/'.$destinationCtrl.'?id='.$instance->getAttribute($clef_destination_ctrl).''.$datas_complementaires_postees;
                                    } else {
                                        $flag_lien = '/'.$destinationCtrl.'?id='.$instance->getAttribute($clef).''.$datas_complementaires_postees;
                                    }
                                    if ($ouverture_nouvelle_page) {
                                        $flag_lien = "onclick=\"window.open('".$flag_lien."')\"";
                                    } else {
                                        $flag_lien = "onclick=\"window.location.href='".$flag_lien."'\"";
                                    }
                                } else {
                                    $flag_lien = '/'.$classe::getControllerName().'/editer?id='.$instance->getAttribute($clef).''.$datas_complementaires_postees;
                                    if ($ouverture_nouvelle_page) {
                                        $flag_lien = "onclick=\"window.open('".$flag_lien."')\"";
                                    } else {
                                        $flag_lien = "onclick=\"window.location.href='".$flag_lien."'\"";
                                    }
                                }
                            }
                        }

                        if (!isset($tableau_ligne[$cpt_ligne]["liste_ids_de_la_ligne"])) {
                            $tableau_ligne[$cpt_ligne]["liste_ids_de_la_ligne"] = 0;    // utiliser pour le CSS et le hover sur toutes les cases d'une même ligne
                        }

                        if (isset($_REQUEST['xls'])) {
                            echo '<td style="'.$style.'" >'.$valeur.'</td>';
                        } else {

                            if (!isset($tableau_colonne[$nom]["html"])) {
                                $tableau_colonne[$nom]["html"] = "";
                                $tableau_colonne[$nom]["nb_ligne"] = 0;
                                $tableau_colonne[$nom]["max_largeur"] = 0;
                            }

                            $style .= 'left:0;width:100%;top:'.$haut.'px;';

                            $largeur = (int) str_replace($unite, "", $largeur);
                            if ($unite != "%") {
                                $largeur--;
                            }
                            $tableau_colonne[$nom]["largeur"] = $largeur;
                            $tableau_colonne[$nom]["left"] = $left;
                            $tableau_colonne[$nom]["nb_ligne"]++;
                            $tableau_colonne[$nom]["max_largeur"] = max($tableau_colonne[$nom]["max_largeur"], strlen($valeur));

                            $cpt_case++;

                            $click_cligne = "";
                            if ($clef && $flag_lien) {
                                $click_cligne = $flag_lien;
                            }

                            $tableau_colonne[$nom]["html"] .= "<div id='case_".$cpt_case."' ".$title." style='".$style."' class='bordure ".$class_ligne."' onmouseover=\"gestionHoverLigne('".$cpt_ligne."','".$class_ligne_on."');\" onmouseout=\"gestionHoverLigne('".$cpt_ligne."','".$class_ligne_off."')\" ".$click_cligne." >&nbsp;";
                            /*
                             if ($clef && $flag_lien) {
                                $tableau_colonne[$nom]["html"] .= "<a href='".$flag_lien."'>".$valeur."</a>";
                            } else {
                                $tableau_colonne[$nom]["html"] .= $valeur;
                            }
                            */
                            $tableau_colonne[$nom]["html"] .= $valeur;
                            $tableau_colonne[$nom]["html"] .= "&nbsp;</div>";

                            if ($premiere) {
                                $tableau_colonne[$nom]["html"] .= '<input type="hidden" value="'.$nb_objets.'" name="nb_elements_total" id="nb_elements_total_'.$_REQUEST['unique_id'].'" />';
                                $premiere = false;
                            }

                            $tableau_ligne[$cpt_ligne]['liste_ids_de_la_ligne'] .= ','.$cpt_case;

                        }
                        if ($unite != "%") {
                            $left += $largeur + 1;
                        } else {
                            $left += $largeur;
                        }
                    }

                    $haut += 20;
                    if (isset($_REQUEST['xls'])) {
                        echo '</tr>';
                    } else {
                        //on va rajouter dans un champ cachée, la liste des ID des div de la ligne
                        //on s'en servira pour la gestion du "hover" des lignes avec la souris
                        echo '<input type="hidden" id="num_ligne_'.$cpt_ligne.'" value="'.$tableau_ligne[$cpt_ligne]['liste_ids_de_la_ligne'].'" >';
                    }
                }

                $memo_nom_colonne = "";
                foreach ($tableau_colonne as $nom => $colonne) {
                    echo "<div id=\"div_liste_colonne_".$_REQUEST['unique_id']."_".$nom."\" class=\"liste_div_colonne bordure\" style=\"height:".($tableau_colonne[$nom]["nb_ligne"] * 20)."px;left:".$tableau_colonne[$nom]["left"].$unite.";width:".$tableau_colonne[$nom]["largeur"].$unite.";\">";
                        echo $colonne["html"];
                        echo "<div id=\"div_redimensionne_liste_colonne_".$_REQUEST['unique_id']."_".$nom."\" class=\"redimensionne\">";
                            echo "<input type=\"hidden\" id=\"max_largeur_".$_REQUEST['unique_id']."_".$nom."\" value=\"".$tableau_colonne[$nom]["max_largeur"]."\" >";
                        echo "</div>";
                    echo "</div>";


                    $memo_nom_colonne = $nom;
                }

            } else {
                //div aussi large que la somme des colonnes pour forcer l'affichage de la scrollbar
                $largeur_total = 0;
                for ($i = 0; $i < count($tableau_champs); $i++) {
                    $largeur = '200';
                    $largeur_total += $largeur;
                }
                /*
                foreach ($tableau_champs as $instance->champHTML) {
                debug::output($instance->champHTML);
                    if (isset($instance->champHTML['largeur'])) {
                        $largeur = $instance->champHTML['largeur'];
                    } else {
                        $largeur = '200';
                    }
                    $largeur_total += $largeur;
                }
                */

                echo '<div style="height:1px;overflow:hidden;width:'.$largeur_total.$unite.';"></div>';

                //div pas de resultat
                echo '<div style="font:12px Verdana;text-align:center;">- Pas de résultat -</div>';
                echo '<input type="hidden" value="0" name="nb_elements_total" id="nb_elements_total_'.$_REQUEST['unique_id'].'" />';

            }

            if ($nb_objets == 0 || $top == 0) {
                $numero_page = 1;
                $nb_page = 1;
            } else {
                $nb_page = ceil($nb_objets / $top);
            }

            echo '<script>';
            echo '$("numero_page_'.$_REQUEST['unique_id'].'").innerHTML = "'.$numero_page.'";';
            echo '$("filtre_liste_nb_page_'.$_REQUEST['unique_id'].'").innerHTML = "'.$nb_page.'";';
            if ($numero_page == 1) {
                echo '$("filtre_liste_img_page_precedente_'.$_REQUEST['unique_id'].'").style.display = "none";';
            } else {
                echo '$("filtre_liste_img_page_precedente_'.$_REQUEST['unique_id'].'").style.display = "";';
            }
            if ($numero_page == $nb_page) {
                echo '$("filtre_liste_img_page_suivante_'.$_REQUEST['unique_id'].'").style.display = "none";';
            } else {
                echo '$("filtre_liste_img_page_suivante_'.$_REQUEST['unique_id'].'").style.display = "";';
            }
            echo '</script>';

        } else {
            echo '<script>';
            echo 'location.reload();';
            echo '</script>';
        }
        if (isset($_REQUEST['xls'])) {
            echo "</table></body></html>";
        }
    }
}
