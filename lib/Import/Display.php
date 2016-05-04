<?php
namespace FMUP\Import;

use FMUP\Import\Iterator\DoublonIterator;
use FMUP\Import\Iterator\LineToConfigIterator;
use FMUP\Import\Iterator\ValidatorIterator;

/**
 *
 * @author csanz
 *
 */
abstract class Display extends \FMUP\Import
{
    private $totalInsert;
    private $totalUpdate;
    private $totalErrors;

    /**
     *
     * @return int
     */
    public function getTotalUpdate()
    {
        return (int)$this->totalUpdate;
    }

    /**
     *
     * @return int
     */
    public function getTotalInsert()
    {
        return (int)$this->totalInsert;
    }

    /**
     *
     * @return int
     */
    public function getTotalErrors()
    {
        return (int)$this->totalErrors;
    }

    public function parse()
    {
        try {
            $lci = new LineToConfigIterator($this->fileIterator, $this->config);
            $di = new DoublonIterator($lci);
            $vi = new ValidatorIterator($di);
            foreach ($vi as $key => $value) {
                if ($value) {
                    $this->displayImport($value, $vi, $di, $lci, $key);
                }
            }
            $this->totalErrors = $vi->getTotalErrors();
            $this->totalInsert = $vi->getTotalInsert();
            $this->totalUpdate = $vi->getTotalUpdate();
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
    abstract public function displayImport(
        Config $value,
        ValidatorIterator $vi,
        DoublonIterator $di,
        LineToConfigIterator $lci,
        $key
    );
}
