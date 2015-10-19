<?php
define('BASE_PATH', __DIR__ . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array(
    "..",
    "..",
    ".."
)));
require_once BASE_PATH . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . 'framework.php';

global $sys_controller_instance;
$sys_controller_instance = new Controller();

use \FMUP\Import\Config;
use FMUP\Import;
use FMUP\Import\Config\Field;
use FMUP\Import\Config\ConfigObjet;
use FMUP\Import\Config\Field\Formatter\IdFromField;
use FMUP\Import\Config\Field\Formatter\DateSQL;

$testConfig = new Config();

$value = "";


$f0 = new Field("indicatif", $value, false, false, true, "");
$f1 = new Field("qualite", $value, "utilisateurs", "id_civilite", true, "id");
$f1->addFormatterDebut(new IdFromField("code", "civilites"));
$f2 = new Field("nom", $value, "utilisateurs", "nom", true, "");
$f3 = new Field("prenom", $value, "utilisateurs", "prenom", true, "");
$f4 = new Field("adresse1", $value, "utilisateurs", "adresse1", false, "");
$f5 = new Field("adresse2", $value, "utilisateurs", "adresse2", false, "");
$f6 = new Field("adresse3", $value, "utilisateurs", "adresse3", false, "");
$f7 = new Field("cp", $value, "utilisateurs", "code_postal", false, "alphanum");
$f8 = new Field("ville", $value, "utilisateurs", "ville", false, "");
$f9 = new Field("pays", $value, "utilisateurs", "id_pays", false, "id");
$f9->addFormatterDebut(new IdFromField("libelle", "pays"));
$f10 = new Field("nom_jeune_fille", $value, false, false, false, "");
$f11 = new Field("numero_DAEFLE", $value, false, false, true, "integer");
$f12 = new Field("telephone", $value, "utilisateurs", "telephone_fixe", false, "telephone");
$f13 = new Field("courriel", $value, "utilisateurs", "email", true, "email");
$f14 = new Field("classe", $value, false, false, false, "");
$f15 = new Field("libelle_classe", $value, false, false, false, "");
$f16 = new Field("inscription", $value, false, false, false, "date");
$f17 = new Field("naissance", $value, "utilisateurs", "date_naissance", false, "date");
$f17->addFormatterFin(new DateSQL());
$f18 = new Field("lieu_naissance", $value, false, false, false, "");
$f19 = new Field("statut", $value, false, false, false, "alphanum");
$f20 = new Field("categorie", $value, false, false, false, "alphanum");
$f21 = new Field("CSP", $value, false, false, false, "alphanum");
$f22 = new Field("div", $value, false, false, false, "alphanum");
$f23 = new Field("option1", $value, false, false, false, "alphanum");
$f24 = new Field("option2", $value, false, false, false, "alphanum");
$f25 = new Field("option3", $value, false, false, false, "alphanum");
$f26 = new Field("option4", $value, false, false, false, "alphanum");
$f27 = new Field("option5", $value, false, false, false, "alphanum");
$f28 = new Field("option6", $value, false, false, false, "alphanum");
$f29 = new Field("option7", $value, false, false, false, "alphanum");
$f30 = new Field("option8", $value, false, false, false, "alphanum");
// libelle du module
$f31 = new Field("matiere", $value, "modules", "code", true, "alphanum");
$f32 = new Field("devoirs", $value, false, false, true, "");
$f33 = new Field("presence_animation", $value, false, false, true, "alphanum");
$f34 = new Field("recu", $value, false, false, true, "boolean");
$f35 = new Field("total_tutorat", $value, false, false, true, "integer");
$f36 = new Field("presence_tutorat", $value, false, false, true, "integer");
$f37 = new Field("presence_tutorat_methodo", $value, false, false, true, "integer");

$nb_colonne = 38;

for ($i = 0; $i < $nb_colonne; $i ++) {
    $name = "f" . $i;
    $testConfig->addField($$name);
}

$objet_module = new ConfigObjet("Module", 2);
$objet_module->addIndex(31);
$objet_utilisateur = new ConfigObjet("Utilisateur", 1);
$objet_utilisateur->addIndex(1);
$objet_utilisateur->addIndex(2);
$objet_utilisateur->addIndex(3);
$objet_utilisateur->addIndex(4);
$objet_utilisateur->addIndex(12);
$objet_utilisateur->addIndex(13);
$objet_utilisateur->addIndex(17);

$testConfig->addConfigObjet($objet_utilisateur);
// $testConfig->addConfigObjet($objet_module);

$testImport = new Import(__DIR__ . DIRECTORY_SEPARATOR . $argv[1], $testConfig);
$testImport->parse();
?>