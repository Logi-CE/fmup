<?php
namespace FMUP\Import\Iterator;

use FMUP\Import\Config;

/**
 * Valide une ligne et compte le nombre de ligne MAJ ou CRÉÉ
 *
 * @author csanz
 *
 */
class ValidatorIterator extends \IteratorIterator
{

    /**
     * Si la ligne est validée
     *
     * @var bool
     */
    private $valid;

    /**
     * Si l'import va réaliser uniquement des insert, le type sera "insert" sinon "update"
     *
     * @var string
     */
    private $lineType;

    /**
     *
     * @var integer
     */
    private $totalInsert = 0;

    /**
     *
     * @var Integer
     */
    private $totalUpdate = 0;

    /**
     *
     * @var integer
     */
    private $totalErrors = 0;

    /*
     * ***************************
     * GETTERS
     * ***************************
     */

    /**
     *
     * @return bool
     */
    public function getValid()
    {
        return (bool)$this->valid;
    }

    /**
     *
     * @return string
     */
    public function getType()
    {
        return $this->lineType;
    }

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

    public function next()
    {
        parent::next();
        $current = $this->current();
        if (!$current || !$current instanceof Config) {
            return;
        }
        $this->valid = $current->validateLine();
        $type = "";
        foreach ($current->getListeConfigObjet() as $configObject) {
            /* @var $configObject \FMUP\Import\Config\ConfigObjet */
            if ($configObject->getStatut() == "insert") {
                $type = ($type == "update" ? "update" : "insert");
            } elseif ($configObject->getStatut() == "update") {
                $type = "update";
            }
        }
        if ($this->valid && !$current->getDoublonLigne()) {
            if ($type == "insert") {
                $this->totalInsert++;
            } elseif ($type == "update") {
                $this->totalUpdate++;
            }
        } else {
            $this->totalErrors++;
        }
        $this->lineType = $type;
    }
}
