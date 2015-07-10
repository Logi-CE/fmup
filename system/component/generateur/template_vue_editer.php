<form id="formulaire_details" action="./<?php echo $nom_fichier_court; ?>/editer" method="post">
    <h1><?php echo '<?php echo LangueHelper::display($titre); ?>'; ?></h1>

    <div id="div_boutons">
        <?php echo '<?php if ($editable) : ?>'."\n"; ?>
            <?php echo '<?php echo Bouton::enregistrer(); ?>'."\n"; ?>
            <?php echo '<?php if ($'.$objet.'->getId()) : ?>'."\n"; ?>
                <?php echo '<?php echo Bouton::supprimer(); ?>'."\n"; ?>
            <?php echo '<?php endif; ?>'."\n"; ?>
        <?php echo "<?php elseif (Utilisateur::getUtilisateurConnecte()->getDroits('".$nom_fichier."', 'ecriture')) : ?>"."\n"; ?>
        	<a href="./<?php echo $nom_fichier_court; ?>/editer?id=<?php echo '<?php echo $'.$objet.'->getId(); ?>'; ?>"><?php echo '<?php echo Bouton::editer(); ?>'; ?></a>
        <?php echo '<?php endif; ?>'."\n"; ?>
        <a href="./<?php echo $nom_fichier_court; ?>/filtrer"><?php echo '<?php echo Bouton::retourListe(); ?>'; ?></a>
    </div>

    <div id="corps_edit">
        <fieldset>
            <?php echo '<?php echo DisplayHelper::errorsFor($'.$objet.'); ?>'."\n"; ?>
            <div class="colonne_gauche">
<?php foreach ($champs as $libelle => $filtre) :	?>
<?php if (!empty($filtre['presence_edition'])) : ?>
<?php $readonly = (!empty($filtre['editable'])) ? "$"."readonly" : 'readonly'; ?>
            <div class="champ_edit">
                <label for="<?php echo $objet; ?>_<?php echo $libelle; ?>"><?php echo "<?php echo LangueHelper::display('".$filtre['nom']."'); ?>"; ?></label>
                <p>
                    <?php echo "<?php echo FormHelper::inputText($".$objet.", '".$libelle."', $"."editable, array()); ?>"."\n"; ?>
                </p>
            </div>
<?php endif; ?>
<?php endforeach; ?>
            </div>
    
            <div class="colonne_droite">
            </div>
            <?php echo "<?php echo FormHelper::inputHidden($".$objet.", 'id'); ?>"."\n"; ?>
            <?php echo '<?php echo FormHelper::getInputToken(); ?>'."\n"; ?>
        </fieldset>
    </div>
</form>
