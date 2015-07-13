<div id="formulaire_filtre_<?php echo $this->unique_id; ?>">
    <input type="hidden" name="unique_id" id="unique_id" value="<?php echo $this->unique_id; ?>" />
    <input type="hidden" name="ordre_<?php echo $this->unique_id; ?>" id="ordre_<?php echo $this->unique_id; ?>" value="<?php echo $this->ordre; ?>" />
    <input type="hidden" id="unite_<?php echo $this->unique_id; ?>" value="<?php echo $this->unite; ?>" />

    <?php if (!empty($this->filtre_complementaire)) : ?>
        <fieldset class="conteneur_filtre_supplementaire" id="filtres_complementaires_<?php echo $this->unique_id; ?>">
            <?php foreach ($this->filtre_complementaire as $cpt => $champ) : ?>
                <div class="bloc_filtre_supplementaire" style="left:<?php echo $champ['taille_utilisee']; ?>px;width:<?php echo $champ['largeur']; ?>px;">
                    <?php if ($champ['affichage_libelle'] == 'vertical') : ?>
                        <label class="libelle_filtre_bk" style="top:0;width:<?php echo ($champ['largeur'] - 10); ?>px;">
                            <?php echo $champ['libelle']; ?>
                        </label>
                        <div class="input_filtre_bk" style="top:20px;left:0;">
                            <?php echo $this->afficherFiltre($champ, 'A'.$cpt); ?>
                        </div>
                    <?php else : ?>
                        <label class="libelle_filtre_bk" style="top:10px;width:<?php echo (($champ['largeur'] - 10) / 2); ?>px;">
                            <?php echo $champ['libelle']; ?>
                        </label>
                        <div class="input_filtre_bk" style="top:10px;left:<?php echo ($champ['largeur'] / 2); ?>px;width:<?php echo (($champ['largeur'] - 10) / 2); ?>px;">
                            <?php echo $this->afficherFiltre($champ, 'A'.$cpt); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <?php if (!empty($this->bouton_validation['libelle'])) : ?>
            	<button onclick="<?php echo $this->evenement; ?>" class="btn_validation_formulaire"><?php echo $this->bouton_validation['libelle']; ?></button>
            <?php endif; ?>
        </fieldset>
    <?php endif; ?>
    
    <div class="conteneur_filtre <?php echo $this->classe_filtre; ?>">
        <div class="filtre_div_entete" id="div_filtre_entete_<?php echo $this->unique_id; ?>" ondblclick="autoRedimensionneColonne(event, '<?php echo $this->unique_id; ?>')" onmouseup="finRedimensionneColonne()" onmousemove="redimensonneColonne(event)" onmousedown="debutRedimensionneColonne(event, '<?php echo $this->unique_id; ?>')">
            <?php foreach ($this->colonnes_fusion as $colonne_fusion) : ?>
                <div class="entete_div_colonne filtre_libelle" style="left:<?php echo $colonne_fusion['decalage_gauche'].$this->unite; ?>;width: <?php echo $colonne_fusion['largeur_totale'].$this->unite; ?>; height: 20px;">
                    <?php echo $colonne_fusion['libelle']; ?>
                </div>
            <?php endforeach; ?>

            <?php foreach ($this->tableau_champs as $cpt => $champ) : ?>
                <div class="entete_div_colonne" id="div_entete_colonne_<?php echo $this->unique_id; ?>_<?php echo $champ['nom']; ?>" style="left:<?php echo $champ['left'].$this->unite; ?>; width: <?php echo $champ['largeur'].$this->unite; ?>;top: <?php echo $champ['top_plus']; ?>" title="<?php echo $champ['libelle_title']; ?>">
                    <div class="filtre_libelle" ><?php echo $champ['libelle']; ?></div>

                    <?php if ($champ['ordre']) : ?>
                        <div class="img_ordre" id="img_ordre_<?php echo $this->unique_id; ?>_<?php echo $champ['nom']; ?>" title="Trier" onclick="changeTri('<?php echo $this->unique_id; ?>', '<?php echo $champ['nom']; ?>')"></div>
                    <?php endif; ?>

                    <div class="filtre_div_input" style="<?php echo $champ['top_moins']; ?>">
                        <?php if ($champ['filtre']) : ?>
                            <?php echo $this->afficherFiltre($champ, $cpt); ?>
                        <?php elseif ($champ['checkbox']) : ?>
                            <div style="text-align: center; margin: 3px 0 0 2px;">
                            	<input title="<?php echo LangueHelper::display('Tout cocher/décocher'); ?>" onclick="cocherTout('checkbox_<?php echo $this->unique_id; ?>_<?php echo $champ['nom']; ?>', this);" type="checkbox" value="0" />
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="redimensionne"></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="export_excel" onclick="exportXLS('<?php echo $this->unique_id; ?>')" class="fa fa-file-excel-o fa-lg" title="<?php echo LangueHelper::display('Exporter la liste sous excel'); ?>"></div>
    </div>
    <?php echo FormHelper::getInputToken(); ?>

    <div
            ondblclick="autoRedimensionneColonne(event, '<?php echo $this->unique_id; ?>')"
            onmouseup="finRedimensionneColonne()"
            onmousemove="redimensonneColonne(event)"
            onmousedown="debutRedimensionneColonne(event, '<?php echo $this->unique_id; ?>')"
            class="conteneur_liste <?php echo $this->classe_filtre; ?>"
            id="div_filtre_liste_<?php echo $this->unique_id; ?>"
            onscroll="deplaceEnteteFiltre('<?php echo $this->unique_id; ?>')"
        >
    </div>
    <div class="conteneur_pagination" <?php if (!$this->afficher_page) echo 'style="display:none"'; ?> >
        <div class="div_filtre_liste_paging" id="div_paging_<?php echo $this->unique_id; ?>">
            <span class="fleche_gauche fa fa-backward fa-lg" id="filtre_liste_img_page_precedente_<?php echo $this->unique_id; ?>" onclick="filtreListePagePrecedente('<?php echo $this->unique_id; ?>')"></span>
            <span><?php echo LangueHelper::display('Page'); ?></span>
            <input type="text" id="numero_page_<?php echo $this->unique_id; ?>" onkeyup="changerPage('<?php echo $this->unique_id; ?>')" class="numero_page" name="numero_page" value="<?php echo $numero_page; ?>" />
            <span>/</span>
            <span id="filtre_liste_nb_page_<?php echo $this->unique_id; ?>"></span>
            <span class="fleche_droite fa fa-forward fa-lg" id="filtre_liste_img_page_suivante_<?php echo $this->unique_id; ?>" onclick="filtreListePageSuivante('<?php echo $this->unique_id; ?>')"></span>
        </div>
    
        <?php echo LangueHelper::display('Nb. lignes affichées'); ?> :
        <select name="top" id="top_<?php echo $this->unique_id; ?>" onchange="$('numero_page_<?php echo $this->unique_id; ?>').value='1';filtre('<?php echo $this->unique_id; ?>')" class="nombre_par_page">
            <?php foreach ($this->nombre_par_ligne as $nombre => $libelle) : ?>
                <option value="<?php echo $nombre; ?>" <?php if ($this->nb_lignes_affichees == $nombre) echo 'selected="selected"'; ?>><?php echo $libelle; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>