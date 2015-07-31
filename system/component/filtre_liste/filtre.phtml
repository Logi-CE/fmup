<div id="formulaire_filtre_<?php echo $this->unique_id; ?>">
    <input type="hidden" name="unique_id" id="unique_id" value="<?php echo $this->unique_id; ?>" />
    <input type="hidden" name="ordre_<?php echo $this->unique_id; ?>" id="ordre_<?php echo $this->unique_id; ?>" value="<?php echo $this->ordre; ?>" />
    <input type="hidden" id="unite_<?php echo $this->unique_id; ?>" value="<?php echo $this->unite; ?>" />

    <?php if (!empty($this->filtre_complementaire)) : ?>
        <div class="conteneur_filtre_supplementaire" id="filtres_complementaires_<?php echo $this->unique_id; ?>">
            <?php foreach ($this->filtre_complementaire as $champ) : ?>
                <div class="bloc_filtre_supplementaire" style="left:<?php echo $champ['taille_utilisee']; ?>px;width:<?php echo $champ['largeur']; ?>px;">
                    <?php if ($champ['affichage_libelle'] == 'vertical') : ?>
                        <div class="libelle_filtre_bk" style="top:0;width:<?php echo ($champ['largeur'] - 10); ?>px;">
                            <?php echo $champ['libelle']; ?>
                        </div>
                        <div class="input_filtre_bk" style="top:20px;left:0;">
                            <?php echo $this->afficheFiltre($champ); ?>
                        </div>
                    <?php else : ?>
                        <div class="libelle_filtre_bk" style="top:10px;width:<?php echo (($champ['largeur'] - 10) / 2); ?>px;">
                            <?php echo $champ['libelle']; ?>
                        </div>
                        <div class="input_filtre_bk" style="top:10px;left:<?php echo ($champ['largeur'] / 2); ?>px;width:<?php echo (($champ['largeur'] - 10) / 2); ?>px;">
                            <?php echo $this->afficheFiltre($champ); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div class="conteneur_filtre" style="<?php echo implode(';', $this->style_filtre); ?>">
        <div class="bordure filtre_div_entete degrade" id="div_filtre_entete_<?php echo $this->unique_id; ?>" ondblclick="autoRedimensionneColonne(event, '<?php echo $this->unique_id; ?>')" onmouseup="finRedimensionneColonne()" onmousemove="redimensonneColonne(event)" onmousedown="debutRedimensionneColonne(event, '<?php echo $this->unique_id; ?>')">
            <?php foreach ($this->colonnes_fusion as $colonne_fusion) : ?>
                <div class="entete_div_colonne bordure filtre_libelle" style="left:<?php echo $colonne_fusion['decalage_gauche'].$this->unite; ?>;width: <?php echo $colonne_fusion['largeur_totale'].$this->unite; ?>; height: 20px;">
                    <?php echo $colonne_fusion['libelle']; ?>
                </div>
            <?php endforeach; ?>

            <?php foreach ($this->tableau_champs as $cpt => $champ) : ?>
                <div class="entete_div_colonne bordure" id="div_entete_colonne_<?php echo $this->unique_id; ?>_<?php echo $champ['nom']; ?>" style="left:<?php echo $champ['left'].$this->unite; ?>; width: <?php echo $champ['largeur'].$this->unite; ?>;top: <?php echo $champ['top_plus']; ?>" title="<?php echo $champ['libelle_title']; ?>">
                    <div class="filtre_libelle" ><?php echo $champ['libelle']; ?></div>

                    <?php if ($champ['affichage_ordre']) : ?>
                        <div class="img_ordre" id="img_ordre_<?php echo $this->unique_id; ?>_<?php echo $champ['nom']; ?>" title="Trier" onclick="changeTri('<?php echo $this->unique_id; ?>', '<?php echo $champ['nom']; ?>')"></div>
                    <?php endif; ?>

                    <div class="filtre_div_input" style="<?php echo $champ['top_moins']; ?>">
                        <?php if ($champ['filtre']) : ?>
                            <?php echo $this->afficheFiltre($champ); ?>
                        <?php elseif ($champ['checkbox']) : ?>
                            <div style="text-align: center; margin: 3px 0 0 2px;"><input title="Tout cocher/décocher" onclick="cocherTout('checkbox_<?php echo $this->unique_id; ?>_<?php echo $champ['nom']; ?>', this);" type="checkbox" value="0" id="" /></div>
                        <?php endif; ?>
                    </div>

                    <div class="redimensionne"></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="degrade bordure div_xls">
            <img style="cursor:pointer;" onclick="exportXLS('<?php echo $this->unique_id; ?>')" id="export_excel" title="Exporter la liste sous excel" src="/images/filtre_liste/ico_excel.gif" />
            <input type="hidden" id="liste_colonne_<?php echo $this->unique_id; ?>" value="<?php echo implode(',', $this->liste_colonne); ?>" />
        </div>

    </div>

    <div
            ondblclick="autoRedimensionneColonne(event, '<?php echo $this->unique_id; ?>')"
            onmouseup="finRedimensionneColonne()"
            onmousemove="redimensonneColonne(event)"
            onmousedown="debutRedimensionneColonne(event, '<?php echo $this->unique_id; ?>')"
            class="conteneur_liste"
            style="<?php echo implode(';', $this->style_liste); ?>"
            id="div_filtre_liste_<?php echo $this->unique_id; ?>"
            onscroll="deplaceEnteteFiltre('<?php echo $this->unique_id; ?>')"
        >
    </div>
    <div class="conteneur_pagination">
        <div class="div_filtre_liste_paging" id="div_paging_<?php echo $this->unique_id; ?>">
            <table>
                <tr>
                    <td><div class="fleche_gauche" id="filtre_liste_img_page_precedente_<?php echo $this->unique_id; ?>" onclick="filtreListePagePrecedente('<?php echo $this->unique_id; ?>')"></div></td>
                    <td>Page</td>
                    <td><input type="text" id="numero_page_<?php echo $this->unique_id; ?>" onkeyup="changerPage('<?php echo $this->unique_id; ?>')" class="numero_page" name="numero_page" value="1" /></td>
                    <td>/</td>
                    <td><div id="filtre_liste_nb_page_<?php echo $this->unique_id; ?>"></div></td>
                    <td><div class="fleche_droite" id="filtre_liste_img_page_suivante_<?php echo $this->unique_id; ?>" onclick="filtreListePageSuivante('<?php echo $this->unique_id; ?>')"></div></td>
                </tr>
            </table>
        </div>
    
        Nb. lignes affichées : 
        <select name="top" id="top_<?php echo $this->unique_id; ?>" onchange="$('numero_page_<?php echo $this->unique_id; ?>').value='1';filtre('<?php echo $this->unique_id; ?>')" class="nombre_par_page">
        <?php foreach ($this->nombre_par_ligne as $nombre => $libelle) : ?>
            <option value="<?php echo $nombre; ?>" <?php (($this->nb_lignes_affichees == $nombre) ? 'selected="selected"' : ''); ?>><?php echo $libelle; ?></option>
        <?php endforeach; ?>
        </select>
    </div>
</div>