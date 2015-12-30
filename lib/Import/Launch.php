<?php
namespace FMUP\Import;

use FMUP\Import\Iterator\DoublonIterator;
use FMUP\Import\Iterator\LineToConfigIterator;
use FMUP\Import\Iterator\ValidatorIterator;

/**
 * Lance l'import effectif
 *
 * @author csanz
 *
 */
class Launch extends \FMUP\Import
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
        $db = \Model::getDb();
        $db->beginTransaction();
        try {
            $lci = new LineToConfigIterator($this->fileIterator, $this->config);
            $di = new DoublonIterator($lci);
            $vi = new ValidatorIterator($di);
            foreach ($vi as $value) {
                if ($value) {
                    $valid = $vi->getValid();
                    if ($valid && !$value->getDoublonLigne()) {
                        $value->insertLine();
                    }
                }
            }
            $this->total_errors = $vi->getTotalErrors();
            $this->total_insert = $vi->getTotalInsert();
            $this->total_update = $vi->getTotalUpdate();
            echo "Import terminé .\n";
            $db->commit();
        } catch (\Exception $e) {
            echo "Une erreur a été détecté lors de l'import.";
            echo $e->getMessage();
            $db->rollback();
        }
    }
}
