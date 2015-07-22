<?php
    set_time_limit(-1);
    define('APPLICATION', '');
	define('BASE_PATH', implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', '..', '..', '..', '..', '..')));
    require_once(BASE_PATH."/vendor/autoload.php");
    require_once( __DIR__ .'/generateur.php');

    $generateur = new Generateur();
    $tables = $generateur->getTables();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <title>Génération de filtre liste</title>
        <style>
            div.code {
                border: 1px dashed #888;
            }
            pre {
                display: inline;
            }
        </style>
        <script>
        function chargerChamps(champ) {
        	document.getElementById('chemin').value = champ;
        	if (document.getElementById('tableau_champs')) {
            	document.getElementById('tableau_champs').innerHTML = '';
        	}
        	document.getElementById('formulaire').submit();
        }
        </script>
    </head>
    <body>
        <form id="formulaire" action="" method="post">
        <div>
            <label for="table_name">Nom de la table</label>
            <select name="table_name" id="table_name" onchange="chargerChamps(this.value);">
                <option value="">* Table *</option>
                <?php foreach ($tables['tableau_table'] as $table) : ?>
                    <option value="<?php echo $table; ?>" <?php  if (isset($_REQUEST['table_name']) && $table == $_REQUEST['table_name']) echo 'selected="selected"'; ?>><?php echo $table; ?></option>
                <?php endforeach; ?>
            </select>
            <br />
            <label for="controller_path">Chemin des vues</label>
            <input name="chemin" id="chemin" value="<?php echo (isset($_REQUEST['chemin']))?$_REQUEST['chemin']:'';?>" /><br />
            
            <button type="button" onclick="location.href='index.php';">Retour</button>
<?php

    if (isset($_REQUEST['table_name'])) :
        $colonnes = $generateur->getColonnesTable($_REQUEST['table_name']);

?>
            <br />
            <label>Champs dans la liste / l'édition</label>
            <table border="1" style="border-collapse:collapse;border-color:#000000;" id="tableau_champs">
                <tr style="font-weight:bold;background-color:#ddddff;color:#005500;">
                    <th>Nom base</th>
                    <th>Nom champ</th>
                    <th>Liste</th>
                    <th>Format</th>
                    <th>Edition</th>
                    <th>Editable</th>
                </tr>
                <?php foreach ($colonnes as $champ) : ?>
                    <tr>
                        <th style="font-weight:bold;background-color:#ddddff;color:#005500;">
                            <?php echo $champ['nom']; ?> - <?php echo $champ['type']; ?>
                        </th>
                        <td>
                            <input type="text" <?php if (isset($_REQUEST['filtres'][$champ['nom']]['nom'])) echo 'value="'.$_REQUEST['filtres'][$champ['nom']]['nom'].'"'; else echo 'value="'.$champ['nom'].'"'; ?> value="<?php echo $champ['nom']; ?>" name="filtres[<?php echo $champ['nom']; ?>][nom]" />
                        </td>
                        <td>
                            <input type="checkbox" <?php if (isset($_REQUEST['filtres'][$champ['nom']]['presence_filtre'])) echo 'checked="checked"'; ?> value="1" name="filtres[<?php echo $champ['nom']; ?>][presence_filtre]" />
                        </td>
                        <td>
                            <select name="filtres[<?php echo $champ['nom']; ?>][type]">
                                <option <?php if (isset($_REQUEST['filtres'][$champ['nom']]['type']) && $_REQUEST['filtres'][$champ['nom']]['type'] == 'texte') echo 'selected="selected"'; ?> value="texte">Standard</option>
                                <option <?php if (isset($_REQUEST['filtres'][$champ['nom']]['type']) && $_REQUEST['filtres'][$champ['nom']]['type'] == 'date') echo 'selected="selected"'; ?> value="date">Date</option>
                                <option <?php if (isset($_REQUEST['filtres'][$champ['nom']]['type']) && $_REQUEST['filtres'][$champ['nom']]['type'] == 'int') echo 'selected="selected"'; ?> value="int">Entier</option>
                                <option <?php if (isset($_REQUEST['filtres'][$champ['nom']]['type']) && $_REQUEST['filtres'][$champ['nom']]['type'] == 'float2') echo 'selected="selected"'; ?> value="float2">Décimal (2)</option>
                                <option <?php if (isset($_REQUEST['filtres'][$champ['nom']]['type']) && $_REQUEST['filtres'][$champ['nom']]['type'] == 'monetaire') echo 'selected="selected"'; ?> value="monetaire">€</option>
                                <option <?php if (isset($_REQUEST['filtres'][$champ['nom']]['type']) && $_REQUEST['filtres'][$champ['nom']]['type'] == 'poids') echo 'selected="selected"'; ?> value="poids">Kg</option>
                                <option <?php if (isset($_REQUEST['filtres'][$champ['nom']]['type']) && $_REQUEST['filtres'][$champ['nom']]['type'] == 'flag') echo 'selected="selected"'; ?> value="flag">flag</option>
                            </select>
                        </td>
                        <td>
                            <input type="checkbox" <?php if (isset($_REQUEST['filtres'][$champ['nom']]['presence_edition'])) echo 'checked="checked"'; ?> value="1" name="filtres[<?php echo $champ['nom']; ?>][presence_edition]" />
                        </td>
                        <td>
                            <?php if ($champ['nom'] == 'id') : ?>
                                NON
                                <input type="hidden" value="1" name="filtres[<?php echo $champ['nom']; ?>][editable]" />
                            <?php else : ?>
                                <input type="checkbox" <?php if (isset($_REQUEST['filtres'][$champ['nom']]['editable'])) echo 'checked="checked"'; ?> value="1" name="filtres[<?php echo $champ['nom']; ?>][editable]" />
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <br />
            <button>Générer</button>
            </div>
        </form>
        <?php if (!empty($_REQUEST['filtres']) && !empty($_REQUEST['chemin']) && !empty($_REQUEST['table_name'])) : ?>
            <div class="code">
            <?php $generateur->genererControleur($_REQUEST['table_name'], $_REQUEST['chemin'], $_REQUEST['filtres']); ?>
            </div>
        <?php endif; ?>
        <?php endif; ?>
    </body>
</html>
