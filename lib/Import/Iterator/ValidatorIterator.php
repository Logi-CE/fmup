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
    private $type_ligne;

    /**
     *
     * @var integer
     */
    private $total_insert = 0;

    /**
     *
     * @var Integer
     */
    private $total_update = 0;

    /**
     *
     * @var integer
     */
    private $total_errors = 0;

    /*
     * ***************************
     * GETTERS
     * ***************************
     */

    /**
     *
     * @return boolean
     */
    public function getValid()
    {
        return $this->valid;
    }

    /**
     *
     * @return string
     */
    public function getType()
    {
        return $this->type_ligne;
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

    /**
     * Valide la ligne et détermine son type
     */
    public function next()
    {
        parent::next();
        if (!$this->current()) {
            return $this;
        }
        $this->valid = $this->current()->validateLine();
        $type = "";
        foreach ($this->current()->getListeConfigObjet() as $configObject) {
            /* @var $configObject \FMUP\Import\Config\ConfigObjet */
            if ($configObject->getStatut() == "insert") {
                $type = ($type == "update" ? "update" : "insert");
            } elseif ($configObject->getStatut() == "update") {
                $type = "update";
            }
        }
        if ($this->valid && !$this->current()->getDoublonLigne()) {
            if ($type == "insert") {
                $this->total_insert++;
            } elseif ($type == "update") {
                $this->total_update++;
            }
        } else {
            $this->total_errors++;
        }
        $this->type_ligne = $type;
        return $this;
    }
}
