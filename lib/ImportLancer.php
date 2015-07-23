<?php
namespace FMU;

use FMUP\Import\Iterator\ValidatorIterator;
use FMUP\Import\Iterator\LineFilterIterator;
use FMUP\Import\Iterator\LineToConfigIterator;
use FMUP\Import\Iterator\FileIterator;
use FMUP\Import\Iterator\DoublonIterator;
use FMUP\Import\Iterator\FMUP\Import\Iterator;

/**
 * Lance l'import effectif
 *
 * @author csanz
 *        
 */
class ImportLancer extends \FMUP\Import
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
        \Model::getDb()->beginTrans();
        try {
            $lci = new LineToConfigIterator($this->fileIterator, $this->config);
            $di = new DoublonIterator($lci);
            $vi = new ValidatorIterator($di);
            foreach ($vi as $key => $value) {
                if ($value) {
                    $valid = $vi->getValid();
                    if ($valid && ! $value->getDoublonLigne()) {
                        $value->insertLine();
                    }
                }
            }
            $this->total_errors = $vi->getTotalErrors();
            $this->total_insert = $vi->getTotalInsert();
            $this->total_update = $vi->getTotalUpdate();
            echo "Import terminé .\n";
            \Model::getDb()->commitTrans();
        } catch (\Exception $e) {
            echo "Une erreur a été détecté lors de l'import.";
            echo $e->getMessage();
            \Model::getDb()->rollbackTrans();
        }
    }
}
?>