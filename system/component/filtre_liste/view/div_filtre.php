<?php if (!empty($filtrage_avance)) : ?>
    <select name="<?php echo $nom_filtre; ?>[operateur]" class="select_operateur" onchange="<?php echo $evenement; ?>">
        <?php foreach ($filtrage_avance as $index => $operateur) : ?>
        	<option value="<?php echo $index; ?>" <?php if ($index === $valeur_par_defaut['operateur']) echo 'selected="selected"'; ?>><?php echo $operateur; ?></option>
        <?php endforeach; ?>
    </select>
    
    <div style="top: 0;position: absolute; left: 35px; right: 2px;">
<?php else : ?>
	<input type="hidden" name="<?php echo $nom_filtre; ?>[operateur]" value="<?php echo $valeur_par_defaut['operateur']; ?>"/>
<?php endif; ?>

<select name="<?php echo $nom_filtre; ?>[nom]" class="select_nom <?php if (count($liste_champs) == 1) echo 'desactive'; ?>"  onchange="<?php echo $evenement; ?>">
	<?php if (is_array($liste_champs)) : ?>
        <?php foreach ($liste_champs as $champ_choisi) : ?>
            <option value="<?php echo $champ_choisi; ?>" <?php if ($champ_choisi == $valeur_par_defaut['nom']) echo 'selected="selected"'; ?>><?php echo LangueHelper::display($champ_choisi); ?></option>
        <?php endforeach; ?>
	<?php endif; ?>
</select>

<?php if (!$combo) : ?>

     <?php if ($champ['nom'] == "COCHE") : ?>

        <div style="width:100%;padding-left:1px;padding-top:3px;" align="center">
            <?php if ($activer_filtrage) : ?>
                <input type="checkbox" name="<?php echo $nom_filtre; ?>[valeur]" value="1" onclick="<?php echo $evenement; ?>" />
            <?php else : ?>
                <input type="checkbox" name="<?php echo $nom_filtre; ?>[valeur]" value="1" />
            <?php endif; ?>
        </div>

    <?php else : ?>
		<?php
        $classe = '';
        if (isset($champ['type'])) {
            switch ($champ['type']) {
                case 'date':
                    $classe = 'calendrier';
                    break;
                default:
            }
        }
        ?>
        <input name="<?php echo $nom_filtre; ?>[valeur]" class="filtre_input <?php echo $classe; ?>" <?php echo $identifiant; ?> type="text" onchange="<?php echo $evenement; ?>" onkeyup="<?php echo $evenement; ?>" value="<?php echo $valeur_par_defaut['valeur']; ?>"/>
    <?php endif; ?>

<?php else : ?>
    <select name="<?php echo $nom_filtre; ?>[valeur]" <?php echo $identifiant; ?> onchange="<?php echo $evenement; ?>" class="filtre_select" />
        <?php foreach ($combo as $option) : ?>
            <?php
            $selected = "";
            if ($valeur_par_defaut['valeur'] === $option['valeur']) {
                $selected = 'selected="selected"';
            } elseif (isset($option['defaut']) && $option['defaut'] == '1') {
                $selected = 'selected="selected"';
            }
            ?>
            <option <?php echo $selected; ?> value="<?php echo $option['valeur']; ?>"><?php echo LangueHelper::display($option['texte']); ?></option>
        <?php endforeach; ?>
    </select>
<?php endif; ?>

<?php if (!empty($filtrage_avance)) : ?>
    </div>
<?php endif; ?>