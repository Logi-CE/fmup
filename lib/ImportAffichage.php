<?php
namespace FMUP;

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
abstract class ImportAffichage extends \FMUP\Import
{


    private $total_insert;

    private $total_update;

    private $total_errors;

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
                    $this->afficherImport($value, $vi, $di, $lci, $key);
                }
            }
            $this->total_errors = $vi->getTotalErrors();
            $this->total_insert = $vi->getTotalInsert();
            $this->total_update = $vi->getTotalUpdate();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Affiche l'import
     * 
     * @param Config $value            
     * @param ValidatorIterator $vi            
     * @param DoublonIterator $di            
     * @param LineToConfigIterator $lci  
     * @param integer $key          
     */
    public abstract function afficherImport($value, $vi, $di, $lci, $key);
}
?>