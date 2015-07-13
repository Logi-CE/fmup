<h1><?php echo '<?php echo LangueHelper::display($titre); ?>'; ?></h1>

<div id="div_boutons">
    <a href="./<?php echo $nom_fichier_court; ?>/editer"><?php echo '<?php echo Bouton::creer(); ?>'; ?></a>
    <?php echo '<?php echo Bouton::exporter(); ?>'; ?>
</div>

<div id="corps_filtre">
<?php echo '<?php'; ?>

$colonnes =
    array(
<?php foreach ($champs as $libelle => $filtre) :	?>
<?php if (!empty($filtre['presence_filtre'])) : ?>
        array(
              'libelle' 	=> LangueHelper::display('<?php echo $filtre['nom']; ?>')
            , 'nom' 		=> '<?php echo $libelle; ?>'
            , 'largeur' 	=> '200px'
            , 'titre' 		=> true
            , 'lien' 		=> true
            , 'type'		=> '<?php echo $filtre['type']; ?>'
        ),
<?php endif; ?>
<?php endforeach; ?>
    );

$params =
    array(
          'filtre'=>''
        , 'clef' => 'id'
        , 'unite' => 'px'
        , 'colonnes' => $colonnes
        , 'classe' => '<?php echo $nom_modele; ?>'
        , 'nom_filtre' => '<?php echo $chemin_vues; ?>'
    );
	
$filtre = new FiltreListe($params);
echo $filtre->filtrer();
<?php echo '?>'; ?>

</div>