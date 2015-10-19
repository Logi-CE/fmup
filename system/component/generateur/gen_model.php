<?php
    set_time_limit(-1);
    define('APPLICATION', '');
	define('BASE_PATH', implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', '..', '..', '..', '..', '..')));
    require_once(BASE_PATH."/vendor/autoload.php");
    require_once(__DIR__."/generateur.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <title>Génération de modèle</title>
    </head>
    <body>
        <?php
            $generateur = new Generateur();
            if (!empty($_REQUEST["nettoyage"])) {
                $generateur->supprimerModeles();
            }
        
            if (isset($_REQUEST["table_name"]) && isset($_REQUEST["class_name"])) {
                $generateur->genererModele($_REQUEST["table_name"], $_REQUEST["class_name"], $_GET['loguer']);
            } elseif (!empty($_POST['generation_massive'])) {
                foreach ($_POST['generation_massive'] as $nom_table => $loguer) {
                    $generateur->genererModele($nom_table, String::toCamlCase($nom_table), $loguer);
                }
            }
        ?>
    </body>
</html>
