<?php
namespace FMU;

use FMUP\Import\Iterator\ValidatorIterator;
use FMUP\Import\Iterator\LineFilterIterator;
use FMUP\Import\Iterator\LineToConfigIterator;
use FMUP\Import\Iterator\FileIterator;
use FMUP\Import\Iterator\DoublonIterator;
use FMUP\Import\Iterator\FMUP\Import\Iterator;

/**
 *
 * @author csanz
 *        
 */
class ImportAffichage extends \FMUP\Import
{

    private $fileIterator;

    private $config;

    private $total_insert;

    private $total_update;

    private $total_errors;

    public function __construct($file_name, \FMUP\Import\Config $config)
    {
        $this->fileIterator = new FileIterator($file_name);
        $this->config = $config;
    }

    /**
     *
     * @return number
     */
    public function getTotalUpdate()
    {
        return $this->total_update;
    }

    /**
     *
     * @return number
     */
    public function getTotalInsert()
    {
        return $this->total_insert;
    }

    /**
     *
     * @return number
     */
    public function getTotalErrors()
    {
        return $this->total_errors;
    }

    public function parse()
    {
        try {
            $lci = new LineToConfigIterator($this->fileIterator, $this->config);
            $di = new DoublonIterator($lci);
            $vi = new ValidatorIterator($di);
            foreach ($vi as $key => $value) {
                if ($value) {
                    // $vi->validateLine();
                    $valid = $vi->getValid();
                    echo "<tr>";
                    if ($value->getDoublonLigne()) {
                        echo "<td style='background-color : red !important; color: white;'>" . $key . "</td>";
                        echo "<td style='background-color : red !important; color: white;'>";
                        echo 'Doublon de la ligne : ' . $value->getDoublonLigne();
                        echo "</td>";
                    } else {
                        if (! $valid) {
                            echo "<td style='background-color : red !important; color: white;'>" . $key . "</td>";
                            echo "<td style='background-color : red !important; color: white;'>Ligne non-valide</td>";
                        }
                    }
                    if ($valid && ! $value->getDoublonLigne()) {
                        $color = "";
                        if ($vi->getType() == "insert") {
                            $color = "green";
                        } elseif ($vi->getType() == "update") {
                            $color = "orange";
                        }
                        $str = "";
                        foreach ($value->getListeConfigObjet() as $config_objet) {
                            $statut;
                            if ($config_objet->getStatut() == "insert") {
                                $statut = "CRÉÉ";
                            } elseif ($config_objet->getStatut() == "update") {
                                $statut = "MAJ";
                            }
                            $str .= $config_objet->getNomObjet() . " : " . $statut . "<br>";
                        }
                        echo "<td style='background-color : " . $color . "; color: white;'>" . $key . "</td>";
                        echo "<td style='background-color : " . $color . "; color: white;'>Ligne valide<br>$str</td>";
                    }
                    foreach ($value->getListeField() as $field) {
                        $tab_error = $value->getErrors();
                        $erreur = isset($tab_error[$field->getName()]) ? $tab_error[$field->getName()] : "OK";
                        $class_color_ligne = "";
                        $class_color = "";
                        $color_font = "";
                        if ($erreur != "OK") {
                            $str_erreur = "";
                            foreach ($field->getErreurs() as $msg_erreur) {
                                $str_erreur .= $msg_erreur . "\n";
                            }
                            echo "<td style='background-color : red !important; color: white;' title=\"" . $str_erreur . "\">" . $field->getValue() . "</td>";
                        } else {
                            echo "<td>" . $field->getValue() . "</td>";
                        }
                    }
                }
                echo "</tr>";
            }
            $this->total_errors = $vi->getTotalErrors();
            $this->total_insert = $vi->getTotalInsert();
            $this->total_update = $vi->getTotalUpdate();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
?>